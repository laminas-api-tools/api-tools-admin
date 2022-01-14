<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception\InvalidArgumentException as ExceptionInvalidArgumentException;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Exception\InvalidArgumentException;
use Laminas\Db\Metadata\Metadata;
use Laminas\Db\Metadata\Object\ColumnObject;
use Laminas\Db\Metadata\Object\ConstraintObject;

use function in_array;
use function strpos;
use function strtolower;
use function strtoupper;
use function ucfirst;

class DbAutodiscoveryModel extends AbstractAutodiscoveryModel
{
    /**
     * @param string $module
     * @param string|int $version
     * @param string $adapterName
     * @return array
     * @throws ExceptionInvalidArgumentException|InvalidArgumentException
     */
    public function fetchColumns($module, $version, $adapterName)
    {
        $tables = [];
        if (! isset($this->config['db']['adapters'])) {
            throw new ExceptionInvalidArgumentException('DB config is missing "db.adapters" subkey');
        }

        $config = $this->config['db']['adapters'];

        $adapter = new Adapter($config[$adapterName]);

        try {
            $metadata = new Metadata($adapter);
        } catch (InvalidArgumentException $e) {
            if (strpos($e->getMessage(), 'Unknown adapter platform') === false) {
                throw $e;
            }
            return [];
        }

        $tableNames = $metadata->getTableNames(null, true);

        foreach ($tableNames as $tableName) {
            if ($this->moduleHasService($module, $version, $tableName)) {
                continue;
            }

            $tableData = [
                'table_name' => $tableName,
            ];
            $table     = $metadata->getTable($tableName);

            $tableData['columns'] = [];

            $constraints = $this->getConstraints($metadata, $tableName);

            /** @var ColumnObject $column */
            foreach ($table->getColumns() as $column) {
                $item = [
                    'name'        => $column->getName(),
                    'type'        => $column->getDataType(),
                    'required'    => ! $column->isNullable(),
                    'filters'     => [],
                    'validators'  => [],
                    'constraints' => [],
                ];

                foreach ($constraints as $constraint) {
                    if ($column->getName() === $constraint['column']) {
                        $item['constraints'][] = ucfirst(strtolower($constraint['type']));

                        switch (strtoupper($constraint['type'])) {
                            case 'PRIMARY KEY':
                                break;
                            case 'FOREIGN KEY':
                                $constraintObj = $this->getConstraintForColumn(
                                    $metadata,
                                    $tableName,
                                    $column->getName()
                                );

                                $validator            = $this->validators['foreign_key'];
                                $referencedColumns    = $constraintObj->getReferencedColumns();
                                $validator['options'] = [
                                    'adapter' => $adapterName,
                                    'table'   => $constraintObj->getReferencedTableName(),
                                    //TODO: handle composite key constraint
                                    'field' => $referencedColumns[0],
                                ];
                                $item['validators'][] = $validator;
                                break;
                            case 'UNIQUE':
                                $validator            = $this->validators['unique'];
                                $validator['options'] = [
                                    'adapter' => $adapterName,
                                    'table'   => $tableName,
                                    'field'   => $column->getName(),
                                ];
                                $item['validators'][] = $validator;
                                break;
                        }
                    }
                }

                if (in_array(strtolower($column->getDataType()), ['varchar', 'text'])) {
                    $item['length'] = $column->getCharacterMaximumLength();
                    if (in_array('Primary key', $item['constraints'])) {
                        unset($item['filters']);
                        unset($item['validators']);
                        $tableData['columns'][] = $item;
                        continue;
                    }
                    $item['filters']             = $this->filters['text'];
                    $validator                   = $this->validators['text'];
                    $validator['options']['max'] = $column->getCharacterMaximumLength();
                    $item['validators'][]        = $validator;
                } elseif (
                    in_array(strtolower($column->getDataType()), [
                        'tinyint',
                        'smallint',
                        'mediumint',
                        'int',
                        'bigint',
                    ])
                ) {
                    $item['length'] = $column->getNumericPrecision();
                    if (in_array('Primary key', $item['constraints'])) {
                        unset($item['filters']);
                        unset($item['validators']);
                        $tableData['columns'][] = $item;
                        continue;
                    }
                    $item['filters'] = $this->filters['integer'];
                }

                $tableData['columns'][] = $item;
            }
            $tables[] = $tableData;
        }
        return $tables;
    }

    /**
     * @param string $tableName
     * @return array
     */
    protected function getConstraints(Metadata $metadata, $tableName)
    {
        $constraints = [];
        /** @var ConstraintObject $constraint */
        foreach ($metadata->getConstraints($tableName) as $constraint) {
            foreach ($constraint->getColumns() as $column) {
                $constraints[] = [
                    'column' => $column,
                    'type'   => $constraint->getType(),
                ];
            }
        }

        return $constraints;
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @return null|ConstraintObject
     */
    protected function getConstraintForColumn(Metadata $metadata, $tableName, $columnName)
    {
        /** @var ConstraintObject $constraint */
        foreach ($metadata->getConstraints($tableName) as $constraint) {
            foreach ($constraint->getColumns() as $column) {
                if ($column === $columnName) {
                    return $constraint;
                }
            }
        }
        return null;
    }
}

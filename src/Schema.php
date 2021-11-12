<?php

namespace Nio\SchemaBuilder;

use Closure;


/**
 * Schema builder
 * 
 * Manipulate tables in current db, this class can generate sql strings
 * 
 * @author Carlos Bumba git:@CarlosNio
 */
final class Schema
{

    /**
     * Create a new table and the fields
     *
     * return a full 'create table' sql string
     *
     * @return string|bool
     **/

    public static function create(string $name, Closure $action)
    {
        $table_obj = new Table;
        $action($table_obj);
        $definitions = $table_obj->results();
        $definitions = str_replace("+", ',', $definitions);
        $sql = "CREATE TABLE {$name} {$definitions}";

        return $sql;
    }



    /**
     * alter a table
     *
     * return a full 'alter table' sql string
     *
     * @return string|bool
     **/

    public static function alter(string $name, Closure $action)
    {
        $table_obj = new Alter;
        $action($table_obj);
        $alter = $table_obj->results();
        $sql = '';
        foreach ($alter as $value) {
            $sql .= "ALTER TABLE {$name} {$value}";
        }

        return $sql;
    }


    /**
     * Drop one or mor tables
     *
     * return a 'drop table' sql string
     *
     * @return string|bool
     **/

    public static function drop($table)
    {
        if (is_array($table)) {
            $sql = '';

            foreach ($table as $value) {
                $sql .= " DROP TABLE {$value}; ";
            }

            return $sql;
        }

        $sql = "DROP TABLE {$table}";

        return $sql;
    }

    
    /**
     * Drop one or mor tables if exists
     *
     * return a 'drop table' sql string
     *
     * @return string|bool
     **/

    public static function dropIfExists($table)
    {
        if (is_array($table)) {
            $sql = '';

            foreach ($table as $value) {
                $sql .= " DROP TABLE IF EXISTS {$value}; ";
            }

            return $sql;
        }

        $sql =  "DROP TABLE IF EXISTS {$table}";

        return $sql;
    }

    /**
     * truncate one or mor tables
     *
     * return a 'truncate table' sql string
     *
     * @return string|bool
     **/

    public static function truncate($table)
    {
        if (is_array($table)) {
            $sql = '';
            foreach ($table as $value)
                $sql .= " TRUNCATE TABLE {$value}; ";
            return $sql;
        }

        $sql =  " TRUNCATE TABLE {$table}; ";

        return $sql;
    }

}
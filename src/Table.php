<?php

namespace Nio\SchemaBuilder;

use Nio\SchemaBuilder\traits\floatType;
use Nio\SchemaBuilder\traits\modifier;
use Nio\SchemaBuilder\traits\setType;
use Nio\SchemaBuilder\traits\util;
use Exception;

class Table
{
    private $dataTypes;

    // datatypes traits
    use floatType, setType;
    // modifier trait
    use modifier;
    // utility trait
    use util;


    /**
     * Register column types
     * 
     */
    private function register(string $key, string $name, int $size = null)
    {
        $this->dataTypes[][$key] = [$name, $size];
        return $this;
    }


    /**
     * create strings
     * 
     */
    private function create_str(array $info)
    {
        $s = '';
        $type = $info['type'];
        $name = $info['name'];
        $size = $info['size'];

        if ($info['class'] == 'integer') {
            if (isset($info['mod']['unsigned'])) {
                $type = "unsigned {$type}";
            }
        }

        if (isset($info['mod']['size'])) {
            $size = $info['mod']['size'];
        }

        if ($size)
            $s .= " `{$name}` {$type}({$size}) ";
        else
            $s .= " `{$name}` {$type} ";

        // make sure that all fields will be not null
        $info['mod']['not null'] = '';

        if (isset($info['mod'])) {
            $this->modifiers($s, $info['mod']);
        }

        return $s;
    }




    // primary
    public function primaryKey()
    {
        $current = $this->currentField();

        if (is_null($current)) {
            throw new Exception('No Collumn selected for Primary Key');
        }

        if (isset($this->pk)) {
            throw new Exception("Duplicated Primary Key");
        }

        $this->pk = $current[0][0];
        return $this;
    }

    public function autoIncrement(string $name = 'ID', $primaryKey = true)
    {
        $this->autoIncrement = $name;
        if ($primaryKey == true) $this->pk = $name;
        return $this;
    }

    // foreign key
    public function foreign_key(string $field, string $reference)
    {
        if(!strpos($reference , '.')) {
            throw new Exception("Referenced Collumn must be divided by dot (ex: Table.Collumn )");
        }

        $this->fk[] = func_get_args();
        return $this;
    }

    // unique
    public function unique(string $name = null)
    {
        $current = $this->currentField();
        $name ??= $current[0][0];
        $this->uniques[] = [$name, $current[0][0]];
        return $this;
    }

    // engine
    public function engine(string $engine)
    {
        $this->engine = $engine;
        return $this;
    }

    // charset
    public function charset(string $charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Process and return the results
     * 
     */
    public function results(bool $onlyFields = false)
    {
        $matrix = $this->dataTypes;
        $str = '';

        if ($matrix) {
            for ($i = 0; $i < count($matrix); $i++) {
                // get a array with information about this column type
                $info = $this->getInfo($matrix[$i]);
                // create strings
                if ($info['class'] == 'float')
                    $str .= $this->floatType($info);
                elseif ($info['class'] == 'set')
                    $str .= $this->setsTypes($info);
                else
                    $str .= $this->create_str($info);
                // separate with plus signal
                $str .= " +";
            }
        }

        if (isset($this->autoIncrement)) {
            $s = "`{$this->autoIncrement}` INT NOT NULL AUTO_INCREMENT +";
            $str = $s . $str;
        }

        // eliminate the last plus signal
        $str = substr($str, 0, strlen($str) - 1);

        if (isset($this->pk)) {
            $str .= "+ PRIMARY KEY (`{$this->pk}`)";
        }

        if (isset($this->fk)) {
            foreach ($this->fk as $info) {
                list($t, $f) = explode(".", $info[1]);
                $s = "+ FOREIGN KEY (`{$info[0]}`) REFERENCES `{$t}` (`{$f}`)";
                $str .= $s;
            }
        }

        if (isset($this->uniques)) {
            $s = '';
            foreach ($this->uniques as $info) {
                list($name, $field) = $info;
                $s .= "UNIQUE `{$name}` (`{$field}`) + ";
            }

            $s = substr($s, 0, strlen($s) - 2);
            $str .= " + {$s}";
        }

        $str = "( {$str})";

        if ($onlyFields) return $str;

        if (isset($this->engine)) {
            $str .= " ENGINE = {$this->engine}";
        } else
            $str .= " ENGINE = InnoDB";

        if (isset($this->charset)) {
            $str .= "  default charset={$this->charset}";
        } else
            $str .= "  default charset=UTF8";

        return $str . ' ;';
    }


    // TEXTUAL
    public function string(string $name)
    {
        $size = 100;
        return $this->register("varchar", $name, $size);
    }

    public function char(string $name)
    {
        $size = 1;
        return $this->register("char", $name, $size);
    }

    public function text(string $name)
    {
        $size = 255;
        return $this->register("text", $name, $size);
    }

    public function tinyText(string $name)
    {
        return $this->register("tinytext", $name);
    }

    public function mediumText(string $name)
    {
        return $this->register("mediumtext", $name);
    }

    public function longText(string $name)
    {
        return $this->register("longtext", $name);
    }

    public function blob(string $name, int $size = 50)
    {
        $size = 50;
        return $this->register("blob", $name, $size);
    }

    // INTEGERS
    public function tinyInt(string $name)
    {
        $size = 2;
        return $this->register('tinyint', $name, $size);
    }

    public function smallInt(string $name)
    {
        $size = 4;
        return $this->register('smallint', $name, $size);
    }

    public function mediumInt(string $name)
    {
        $size = 8;
        return $this->register('mediumint', $name, $size);
    }

    public function int(string $name)
    {
        $size = 11;
        return $this->register('int', $name, $size);
    }

    public function bigInt(string $name)
    {
        $size = 25;
        return $this->register('bigint', $name, $size);
    }



    // DATE
    public function datetime(string $name)
    {
        return $this->register("datetime", $name);
    }

    public function date(string $name)
    {
        return $this->register("date", $name);
    }

    public function timestamp(string $name)
    {
        $size = 14;
        return $this->register("timestamp", $name, $size);
    }

    public function time(string $name)
    {
        return $this->register("time", $name);
    }

    public function year(string $name)
    {
        $size = 4;
        return $this->register("year", $name, $size);
    }
}

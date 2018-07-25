<?php

namespace Ddrv\TDS\Core\Storage;

use Exception;

class Link
{

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $tmp;

    /**
     * @param string $key
     * @param string $dir
     * @param string $tmp
     */
    public function __construct($key, $dir, $tmp)
    {
        $this->key = $key;
        $this->file = $dir.DIRECTORY_SEPARATOR.$this->key.'.php';
        $this->tmp = $tmp;
    }

    /**
     * @return void
     */
    public function delete()
    {
        if (!file_exists($this->file)) return;
        unlink($this->file);
    }

    /**
     * @param string $json
     * @return bool
     * @throws Exception
     */
    public function save($json)
    {
        $tmp = $this->tmp.DIRECTORY_SEPARATOR.uniqid();
        if (file_exists($json)) {
            $json = file_get_contents($json);
        }
        $data = json_decode($json, true);
        $this->check($data);
        $responses = array();
        $parameters = array();
        foreach ($data['responses'] as $response) {
            $responses[$response] = $response;
        }
        if (!empty($data['rules'])) {
            foreach ($data['rules'] as $ruleKey => $rule) {
                foreach ($rule['responses'] as $response) {
                    $responses[$response] = $response;
                }
                foreach ($rule['criteria'] as $criterion) {
                    $parameters[$criterion['parameter']] = $criterion['parameter'];
                }
            }
        }
        $className = 'Link'.mb_strtoupper(md5($this->key));
        $content = '<?php'.PHP_EOL.PHP_EOL;
        $content .= 'namespace Ddrv\TDS\Binary\Link;'.PHP_EOL.PHP_EOL;
        $content .= 'use Ddrv\TDS\Core\Handler;'.PHP_EOL;
        $content .= 'use Ddrv\TDS\Core\Handler\Result;'.PHP_EOL;
        $content .= 'use Ddrv\TDS\Core\Request;'.PHP_EOL;
        $content .= '/**'.PHP_EOL;
        $content .= ' * Class '.$className.PHP_EOL;
        $content .= ' *'.PHP_EOL;
        $content .= ' * Link '.$this->key.'. This file automatically generated by '.__CLASS__.PHP_EOL;
        $content .= ' *'.PHP_EOL;
        $content .= ' * @see https://packagist.org/packages/ddrv/tds'.PHP_EOL;
        $content .= ' * @see https://github.com/ddrv/tds'.PHP_EOL;
        $content .= ' */'.PHP_EOL;
        $content .= 'class '.$className.' extends Handler'.PHP_EOL;
        $content .= '{'.PHP_EOL.PHP_EOL;
        $content .= '    /**'.PHP_EOL;
        $content .= '     * @var string[]'.PHP_EOL;
        $content .= '     */'.PHP_EOL;
        $content .= '    protected $responses = array('.PHP_EOL;
        foreach ($responses as $response) {
            $content .= '        \''.addslashes($response).'\','.PHP_EOL;
        }
        $content .= '    );'.PHP_EOL.PHP_EOL;
        $content .= '    /**'.PHP_EOL;
        $content .= '     * @var array'.PHP_EOL;
        $content .= '     */'.PHP_EOL;
        $content .= '    protected $tokens = array('.PHP_EOL;
        foreach ($data['tokens'] as $token) {
            $content .= '        \''.$token['name'].'\' => array('.PHP_EOL;
            $content .= '            \'in\' => \''.$token['in'].'\','.PHP_EOL;
            $content .= '            \'position\' => \''.$token['position'].'\','.PHP_EOL;
            $content .= '            \'pattern\' => \''.(isset($token['pattern'])?$token['pattern']:'').'\','.PHP_EOL;
            $content .= '            \'match\' => \''.(isset($token['match'])?$token['match']:'').'\','.PHP_EOL;
            $content .= '        ),'.PHP_EOL;
        }
        $content .= '    );'.PHP_EOL.PHP_EOL;
        $content .= '    /**'.PHP_EOL;
        $content .= '     * @param Request $request'.PHP_EOL;
        $content .= '     * @return Result'.PHP_EOL;
        $content .= '     */'.PHP_EOL;
        $content .= '    public function click(Request $request)'.PHP_EOL;
        $content .= '    {'.PHP_EOL;
        $content .= '        $tokens = array();'.PHP_EOL;
        $content .= '        foreach($this->tokens as $name => $token) {'.PHP_EOL;
        $content .= '            $value = $request->param($token[\'in\'], $token[\'position\'], $token[\'pattern\'], $token[\'match\']);'.PHP_EOL;
        $content .= '            $request->set($name, $value);'.PHP_EOL;
        $content .= '            $tokens[$name] = $value;'.PHP_EOL;
        $content .= '        }'.PHP_EOL.PHP_EOL;

        if ($parameters) {
            foreach ($parameters as $parameter) {
                preg_match('/^(?<method>[a-z]+)(\.(?<key>.*))?$/ui', $parameter, $m);
                $method = $m['method'];
                $key = isset($m['key'])?'\''.$m['key'].'\'':'';
                $content .= '        $parameter[\''.$parameter.'\'] = $request->'.$method.'('.$key.');'.PHP_EOL;
            }
        }
        $content .= PHP_EOL;

        if (!empty($data['rules'])) {
            foreach ($data['rules'] as $rule) {
                $and = array();
                $or = array();
                foreach ($rule['responses'] as $response) {
                    $or[] = 'isset($this->responseObjects[\''.$response.'\'])';
                }
                $and[] = '('.implode(' || ', $or).')';
                foreach ($rule['criteria'] as $criterion) {
                    $or = array();
                    $parameter = '$parameter[\''.$criterion['parameter'].'\']';
                    $values = array();
                    $operator = mb_strtolower($criterion['operator']);
                    foreach ($criterion['values'] as $value) {
                        $values[addslashes($value)] = addslashes($value);
                    }
                    switch ($operator) {
                        case 'is':
                            $and[] = 'in_array('.$parameter.', array(\''.implode('\', \'', $values).'\'))';
                            break;
                        case 'not':
                            $and[] = '!in_array('.$parameter.', array(\''.implode('\', \'', $values).'\'))';
                            break;
                        case 'regex':
                            foreach ($values as $value) {
                                $or[] = 'preg_match(\''.$value.'\', '.$parameter.')';
                            }
                            $and[] = '('.implode(' || ', $or).')';
                            break;
                        case 'not regex':
                            foreach ($values as $value) {
                                $or[] = '!preg_match(\''.$value.'\', '.$parameter.')';
                            }
                            $and[] = '('.implode(' || ', $or).')';
                            break;
                        case 'le': //no break
                        case 'lt': //no break
                        case 'ge': //no break
                        case 'gt':
                            foreach ($values as $value) {
                                $or[] = 'version_compare('.$parameter.', \''.$value.'\', \''.$operator.'\')';
                            }
                            $and[] = '('.implode(' || ', $or).')';
                            break;
                    }
                }
                $criteria = $this->getCriteriaString($rule['criteria']);
                $rows = explode(PHP_EOL, $criteria);
                $pad = 0;
                foreach ($rows as $row) {
                    if (mb_strlen($row) > $pad) $pad = mb_strlen($row);
                }
                $content .= '        /*'.PHP_EOL;
                $content .= '         * '.(str_pad('', $pad, '=', STR_PAD_BOTH)).PHP_EOL;
                foreach ($rows as $row) {
                    $content .= '         * '.stripslashes($row).PHP_EOL;
                }
                $content .= '         * '.(str_pad('', $pad, '=', STR_PAD_BOTH)).PHP_EOL;
                $content .= '         */'.PHP_EOL;

                $content .= '        if ('.implode(PHP_EOL.'            && ', $and).PHP_EOL.'        ) {'.PHP_EOL;
                $content .= '            $criteria = \''.str_replace(PHP_EOL, '\'.PHP_EOL'.PHP_EOL.'                .\'', $criteria).'\';'.PHP_EOL;
                $content .= '            return new Result($this->getResponse(array(\''.implode('\', \'', $rule['responses']).'\')), $criteria, $tokens);'.PHP_EOL;
                $content .= '        }'.PHP_EOL.PHP_EOL;
            }
        }

        $content .= '        /*'.PHP_EOL;
        $content .= '         * ======================='.PHP_EOL;
        $content .= '         * Default'.PHP_EOL;
        $content .= '         * ======================='.PHP_EOL;
        $content .= '         */'.PHP_EOL;
        $content .= '        return new Result($this->getResponse(array(\''.implode('\', \'', $data['responses']).'\')), null, $tokens);'.PHP_EOL;
        $content .= '    }'.PHP_EOL;
        $content .= '}'.PHP_EOL;
        file_put_contents($tmp, $content);
        rename($tmp, $this->file);
        return true;
    }

    /**
     * @param array $data
     * @throws Exception
     */
    protected function check($data)
    {
        $data = (array)$data;
        $operators = array(
            'is',
            'not',
            'regex',
            'not regex',
            'le',
            'lt',
            'ge',
            'gt',
        );
        $e = 'link '.$this->key.': ';
        if (!$data) throw new Exception($e.'empty data');
        if (!array_key_exists('responses', $data)) throw new Exception($e.'property responses is a required');
        if (!is_array($data['responses'])) throw new Exception($e.'property responses must be an array');
        foreach ($data['responses'] as $num => $response) {
            if (!preg_match('/^[a-z0-9\-\._]+$/ui', $response)) throw new Exception($e.'incorrect responses.'.$num);
        }
        if (array_key_exists('tokens', $data)) {
            if (!is_array($data['tokens'])) throw new Exception($e.'property tokens must be an array');
            foreach ($data['tokens'] as $num => $token) {
                if (empty($token['name'])) throw new Exception($e.'property tokens.'.$num.'.name is a required');
                if (empty($token['in'])) throw new Exception($e.'property tokens.'.$num.'.in is a required');
                if (!array_key_exists('position', $token)) throw new Exception($e.'property tokens.'.$num.'.position is a required');
                if ($token['name'] != (string)$token['name']) throw new Exception($e.'property tokens.'.$num.'.name must be a string');
                if ($token['in'] != (string)$token['in']) throw new Exception($e.'property tokens.'.$num.'.in must be a string');
                if ($token['position'] != (string)$token['position']) throw new Exception($e.'property tokens.'.$num.'.position must be a string');
            }
        };
        if (array_key_exists('rules', $data)) {
            if (!is_array($data['rules'])) throw new Exception($e.'property rules must be an array');
            foreach ($data['rules'] as $rnum=>$rule) {
                if (empty($rule['responses'] || !is_array($rule['responses']))) throw new Exception($e.'property rules.'.$rnum.'.responses is a required and must be an array');
                foreach ($rule['responses'] as $rsnum=>$response) {
                    if (!preg_match('/^[a-z0-9\-\._]+$/ui', $response)) throw new Exception($e.'incorrect rules.'.$rnum.'.responses.'.$rsnum);
                }
                if (empty($rule['criteria'] || !is_array($rule['criteria']))) throw new Exception($e.'property rules.'.$rnum.'.criteria is a required and must be an array');
                foreach ($rule['criteria'] as $cnum=>$criterion) {
                    if (empty($criterion['parameter'])) throw new Exception($e.'property rules.'.$rnum.'.criteria.'.$cnum.'.parameter is a required');
                    if (empty($criterion['operator'])) throw new Exception($e.'property rules.'.$rnum.'.criteria.'.$cnum.'.operator is a required');
                    if (empty($criterion['values']) || !is_array($criterion['values'])) throw new Exception($e.'property rules.'.$rnum.'.criteria.'.$cnum.'.values is a required and must be an array');
                    if ($criterion['parameter'] != (string)$criterion['parameter']) throw new Exception($e.'property rules.'.$rnum.'.criteria.'.$cnum.'.parameter must be a string');
                    if (!in_array(mb_strtolower($criterion['operator']), $operators)) throw new Exception($e.'property rules.'.$rnum.'.criteria.'.$cnum.'.operator must be one of '.implode(', ',$operators));
                    foreach ($criterion['values'] as $vnum=>$value) {
                        if ($value != (string)$value) throw new Exception($e.'property rules.'.$rnum.'.criteria.'.$cnum.'.values.'.$vnum.' must be a string');
                    }
                }
            }
        };
    }

    protected function getCriteriaString($criteria)
    {
        $data = array();
        $maxRowLen = 1;
        $maxLen = 1;
        foreach ($criteria as $criterion) {
            $row = $criterion['parameter'].' '.$criterion['operator'].' ';
            if (count($criterion['values']) > 1) {
                $len = mb_strlen($row);
                $row .= array_shift($criterion['values']);
                foreach ($criterion['values'] as $value) {
                    $rowLen = $len + mb_strlen($value);
                    if ($rowLen > $maxRowLen) $maxRowLen = $rowLen;
                    $row .= PHP_EOL.str_pad(' ', $len, ' ').$value;
                }
            } else {
                $row .= array_shift($criterion['values']);
                $maxRowLen = mb_strlen($row);
            }
            if ($maxRowLen > $maxLen) $maxLen = $maxRowLen;
            $data[] = addslashes($row);
        }
        return implode(PHP_EOL.str_pad('-', $maxLen, '-').PHP_EOL, $data);
    }
}
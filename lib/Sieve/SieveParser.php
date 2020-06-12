<?php

declare(strict_types=1);

/**
 * @author Holger Dehnhardt <holger@dehnhardt.org>
 *
 * Mail
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Mail\Sieve;

class SieveParser {

    /** @var $parsedTree */
    private $parsedTree;

    /** @var $ruleNumber */
    private $ruleNumber = 0;

    /** @var $sieveControls  */
    private $sieveControls = [
        'require', 'if', 'else', 'elseif', 'stop'
    ];

    /** @var $sieveCommands */
    private $sieveActions = ['keep',
        'fileinto' => 'fileinto',
        'redirect' => '',
        'discard' => '',
        'notify' => 'notify',
        'addheader' => 'editheader',
        'deleteheader' =>'editheader',
        'setflag' => '',
        'deleteflag' => '',
        'removeflag' => ''];

    /** @var $sieveMatchTypes */
    private $sieveMatchTypes = [
        ':contains',
        ':is',
        ':matches'
    ];

    public function __construct() {
    }

    /**
     *
     * @param string $sieveClientFactory
     *
     * @return array;
    */

    public function parse(string $script) : array
    {
        $this->parseMultilineComments($script);
        // $this->parseRules($script);
        return $this->parsedTree;
    }

    private function parseMultilineComments( string $script ) {
        $tokens = preg_split("/(\/\*[^*]*\*\/)/s", $script, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach($tokens as $token){
            $token = trim($token);
            if( stripos($token, "/*" ) === 0 ){
                $this->parsedTree[] = ["type" => "comment", "value" => $token];
            } else {
                $this->parseRules($token);
            }
        }
    }

    private function parseRules( string $script ) {
        $level_0 = array();
        $tokencount=0;
        $rules=0;
        // For scripts with rule naming convention
        // $tokens = explode("# rule:[", $script);
        $tokens = preg_split("/(# rule:[^\{]*\{(?:[^{}]++|(?R))*\})/", $script, -1, PREG_SPLIT_DELIM_CAPTURE);
        if(sizeof($tokens) > 1 && stripos($tokens[0], "# rule:[" ) === false ){
            $header = preg_split("/\r\n/", $tokens[0]);
        }
        // if no rule naming convention found, split by topmost 'if' statements
        if( sizeof($tokens) == 1 ) {
            $tokens = preg_split("/(if[^\{]*\{(?:[^{}]++|(?R))*\})/", $script, -1, PREG_SPLIT_DELIM_CAPTURE);
            if(sizeof($tokens) > 1 && stripos($tokens[0], "if" ) === false ){
                $header = preg_split("/\r\n/", $tokens[0]);
            }
        }
        if( sizeof($header) > 1 ){
            unset($tokens[0]);
            $tokens = array_merge($header, $tokens);
        }
        foreach ($tokens as $token ) {
            $token = trim($token);
            // if we have comments with rulenames
            if( stripos($token, "require" ) === 0 ) {
                $this->parsedTree[] = ["type" => "require", "value" => $token];
            }
            else if ( stripos($token, "# rule:[" ) === 0 || stripos($token, "if" ) === 0 ) {
                $this->ruleNumber++;
                $this->parsedTree[]= $this->parseRule($token);;
            }
            else if ( stripos($token, "#" ) === 0 ) {
                $this->parsedTree[] = ["type" => "comment", "value" => $token];
            }
            $tokencount++;
        }
    }

    private function parseRule(string $rule) : array {
        $level_1 = ["type" => "rule"];
        if( stripos($rule, "# rule:[" ) === 0 ){
            $i = preg_match("/.*\[([^\]]*)\][\n\r]*(.*)/s", $rule, $matches);
            if( $i > 0 ){
                $level_1['name'] = $matches[1];
                $level_1['rule'] = $matches[2];
                $level_1['parsedrule'] = $this->parseRuleBody($matches[2]);
            } else {
                $level_1['name'] = "Rule " . $this->ruleNumber;
                $level_1['rule'] = $rule;
                $level_1['parsedrule'] = $this->parseRuleBody($rule);
            }
        } else {
            $level_1['name'] = "Rule " . $this->ruleNumber;
            $level_1['rule'] = $rule;
            $level_1['parsedrule'] = $this->parseRuleBody($rule);
        }
        return $level_1;
    }

    private function parseRuleBody($ruleBody) : array {
        $level_2 = [];
        $ruleBody = trim($ruleBody);
        //$matched = preg_match("/((if|else|elsif)[^\(]*\([^\)]*\)[^\{]*\{[^\}]*\})/m", $ruleBody, $matches );
        $matched = preg_match_all("/((if|else|elsif)[^}]*})/m", $ruleBody, $matches );
        if( $matched > 0 ){
            foreach( $matches[1] as $match ) {
                $level_2 = $this->parseSingleRuleBody($match);
            }
        }
        return $level_2;
    }

    private function parseSingleRuleBody($ruleBody) : array {
        $level_3 = [];
        $matched = preg_match_all("/((if|else|elsif)[^{]*)/m", $ruleBody, $matches );
        if( $matched > 0 ){
            foreach( $matches[1] as $match) {
                $level_3["conditions"] = $this->parseCondition($match);
            }
        }
        $matched = preg_match_all("/([^;{]*;)/", $ruleBody, $matches );
        if( $matched > 0 ){
            foreach( $matches[1] as $match) {
                $level_3["actions"][] = trim($match);
            }
        }
        return $level_3;
    }

    private function parseCondition($condition) : array {
        $level_4 = array();
        $verbs = preg_split("/ +/", $condition, -1, PREG_SPLIT_DELIM_CAPTURE);
        $level_4['condition-verb'] = trim($verbs[0]," \t\n\r\0\x0B()");
        if( stripos($verbs[1], "anyof") === 0 || stripos($verbs[1], "allof") === 0 ){
            $level_4['testlist']['listtype']=trim($verbs[1]," \t\n\r\0\x0B()");
            $matched=preg_match("/\(([^\)]*)\)/", $condition, $matches);
            if($matched > 0){
                $level_4['testlist']['tests'] = $this->parseConditionComparisonsList(trim($matches[1]));
            }
        } else {
            unset($verbs[0]);
            $level_4['test'] = $this->parseConditionComparisons(trim(implode(" ", $verbs)));
        }
        return $level_4;
    }

    private function parseConditionComparisonsList($condition) : array {
        $level_5 = array();
        //$conditions = preg_split("/[,]/", $condition);
        $conditions = preg_split("/,(?=(?:[^\"\[\]]*[\"\[][^\"\]]*[\"\]])*[^\"\]]*$)/", $condition);
        foreach($conditions as $condition ){
            $level_5[] = $this->parseConditionComparisons(trim($condition));
        }
        return $level_5;
    }

    private function parseConditionComparisons($condition) : array {
        $level_6 = array();
        //$level_6 = preg_split("/ (?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)/", $condition);
        $level_6 = preg_split("/ (?=(?:[^\"\[\]]*[\"\[][^\"\]]*[\"\]])*[^\"\]]*$)/", $condition);
        return $level_6;
    }
}
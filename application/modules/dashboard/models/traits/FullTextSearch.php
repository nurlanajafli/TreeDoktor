<?php


namespace application\modules\dashboard\models\traits;


use application\modules\dashboard\models\Dashboard;

trait FullTextSearch
{
    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
    protected function fullTextWildcards($term)
    {
        preg_match("/.*?([(]{0,1}\d{3}[-.)\t\s]{0,2}(:?([0-9]{2,3}[. -]{0,1}[^a-zA-Z][0-9]{0,3}[. -]{0,1}[^a-zA-Z][0-9]{0,4})|([0-9]{3,9}))).*?/is", $term, $phone_nums);
         
        if(isset($phone_nums[1]) && $phone_nums[1]){
            $phone = str_replace(['-', '+', '<', '>', '@', '(', ')', '~', '.', ' ', '*'], '', $phone_nums[1]);
            $term = str_replace($phone_nums[1], $phone . ' ', $term);
        }

        
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~', '*'];
        //$term = str_replace($reservedSymbols, '', $term);

        $words = explode(' ', $term);
        
        /*foreach($words as $key => $word) {
            if(strlen($word) < 3 && isset($words[$key+1])){
                $words[$key+1] = $word.' '.$words[$key+1];
                unset($words[$key]);
                continue;
            }
            
            if(strlen($word) < 3 && !isset($words[$key+1]) && isset($words[$key-1])){
                $words[$key-1] = $words[$key-1].' '.$word;
                unset($words[$key]);
            }
        }*/

        $words = array_values($words);

        foreach($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            $words[$key] = str_replace('"', '', $words[$key]);
            
            $search_special = false;
            foreach($reservedSymbols as $char) {
                if(array_search($char, str_split($words[$key]))!==false)
                    $search_special = array_search($char, str_split($words[$key]));
            }

            if($search_special!==false)
            {
                if(strlen($words[$key]) >= 2)
                    $words[$key] = '"' .addslashes($word).'*"';
                else{
                    $words[$key] = '';
                }
            }
            else
            {

                $words[$key] = addslashes($words[$key]);

                if(strlen($words[$key]) >= 2 && strpos($words[$key], ' ')!==false)
                    $words[$key] = str_replace(' ', '+', $words[$key]).'*';

                elseif(strlen($words[$key]) >= 2 && strpos($words[$key], ' ')===false)
                    $words[$key] = '+' . $words[$key].'*';

            }


        }
        $searchTerm = implode(' ', $words);

        return $searchTerm;

    }

    /**
     * Scope a query that matches a full text search of term.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $term)
    {
        $columns = implode(',',$this->searchable);

        $searchableTerm = $this->fullTextWildcards($term);
        return $query->selectRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE) AS relevance_score", [$searchableTerm])
        ->whereRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)", $searchableTerm)
        ->orderByDesc('relevance_score');
    }
}

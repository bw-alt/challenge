<?php

    echo foobarLoop(1, 100, 3, 5);

    /**
     * Runs a loop from the provided start and end params, replacing everything
     * divisible by the param foo with foo, bar with bar, and both with foobar
     *
     * @param int $start start of loop
     * @param int $end end of loop
     * @param int $foo numbers to replace with foo
     * @param int $bar numbers to replace with bar
     * @return string
     **/
    function foobarLoop(int $start, int $end, int $foo, int $bar) {
        $result = "";
        for ($i = $start; $i <= $end; $i++) {
            $add = "";
            if ($i % $foo == 0) {
                $add .= "foo";
            }
            if ($i % $bar == 0) {
                $add .= "bar";
            }
            if (!$add) {
                $add .= $i;
            }
            if ($i < $end) {
                $add .= ", ";
            }
            $result .= $add;
        }
        return $result;
    }

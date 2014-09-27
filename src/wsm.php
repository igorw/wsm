<?php

namespace igorw\wsm;

function parse($input) {
    $direct = [
        'dup'       => " \n ",
        'swap'      => " \n\t",
        'discard'   => " \n\n",

        'add'       => "\t   ",
        'sub'       => "\t  \t",
        'mul'       => "\t  \n",
        'div'       => "\t \t ",
        'mod'       => "\t \t\t",

        'store'     => "\t\t ",
        'retrieve'  => "\t\t\t",

        'exit'      => "\n\n\n",
        'ret'       => "\n\t\n",

        'write_char'=> "\t\n  ",
        'write_num' => "\t\n \t",
        'read_char' => "\t\n\t ",
        'read_num'  => "\t\n\t\t",
    ];

    $parameterised = [
        'push' => "  ",
        'ref' => " \t ",
        'slide' => " \t\n",

        'label' => "\n  ",
        'call' => "\n \t",
        'jump' => "\n \n",
        'jumpz' => "\n\t ",
        'jumplz' => "\n\t\t",
    ];

    foreach ($input as $i => $line) {
        $line = preg_replace('/#.*$/', '', $line);
        $line = trim($line);

        if ('' === $line) {
            continue;
        }

        if (isset($direct[$line])) {
            yield $direct[$line];
            continue;
        }

        if (preg_match('/^(push|ref|slide) (-?\d+)$/', $line, $matches)) {
            $inst = $parameterised[$matches[1]];
            $number = (int) $matches[2];
            yield $inst.int_to_signed($number);
            continue;
        }

        if (preg_match('/^(label|call|jump|jumpz|jumplz) (\d+)$/', $line, $matches)) {
            $inst = $parameterised[$matches[1]];
            $number = (int) $matches[2];
            yield $inst.int_to_unsigned($number);
            continue;
        }

        if (preg_match('/^push \'(.)\'$/', $line, $matches)) {
            $inst = $parameterised[$matches[1]];
            $number = (int) ord($matches[2]);
            yield $inst.int_to_signed($number);
            continue;
        }

        if (preg_match('/^store (\d+) (\d+)$/', $line, $matches)) {
            $addr = (int) $matches[1];
            $number = (int) $matches[2];
            yield $parameterised['push'].int_to_signed($addr);
            yield $parameterised['push'].int_to_signed($number);
            yield $direct['store'];
            continue;
        }

        if (preg_match('/^store (\d+) \'(.)\'$/', $line, $matches)) {
            $addr = (int) $matches[1];
            $number = (int) ord($matches[2]);
            yield $parameterised['push'].int_to_signed($addr);
            yield $parameterised['push'].int_to_signed($number);
            yield $direct['store'];
            continue;
        }

        if (preg_match('/^store (\d+) "(.*)"$/', $line, $matches)) {
            $addr = (int) $matches[1];
            foreach (str_split($matches[2]) as $char) {
                $number = (int) ord($char);
                yield $parameterised['push'].int_to_signed($addr);
                yield $parameterised['push'].int_to_signed($number);
                yield $direct['store'];
                $addr++;
            }
            yield $parameterised['push'].int_to_signed($addr);
            yield $parameterised['push'].int_to_signed(0);
            yield $direct['store'];
            continue;
        }

        if (preg_match('/^retrieve (-?\d+)$/', $line, $matches)) {
            $addr = (int) $matches[1];
            yield $parameterised['push'].int_to_signed($addr);
            yield $direct['retrieve'];
            continue;
        }

        if (preg_match('/^write_char (-?\d+)$/', $line, $matches)) {
            $addr = (int) $matches[1];
            yield $parameterised['push'].int_to_signed($addr);
            yield $direct['write_char'];
            continue;
        }

        if (preg_match('/^write_char \'(\w)\'$/', $line, $matches)) {
            $addr = (int) $matches[1];
            yield $parameterised['push'].int_to_signed($addr);
            yield $direct['write_char'];
            continue;
        }

        throw new \InvalidArgumentException(sprintf('Invalid instruction on line %s: %s', $i + 1, json_encode($line)));
    }
}

function int_to_signed($number) {
    $sign = $number >= 0 ? ' ' : "\t";
    $digits = str_replace(['0', '1'], [' ', "\t"],
                base_convert(abs($number), 10, 2));
    return $sign.$digits."\n";
}

function int_to_unsigned($number) {
    $digits = str_replace(['0', '1'], [' ', "\t"],
                base_convert($number, 10, 2));
    return $digits."\n";
}

<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * @package     ProgressBar
 * @copyright   2019 Podvirnyy Nikita (KRypt0n_)
 * @license     GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @license     Enfesto Studio Group license <https://vk.com/topic-113350174_36400959>
 * @author      Podvirnyy Nikita (KRypt0n_)
 * 
 * Contacts:
 *
 * Email: <suimin.tu.mu.ga.mi@gmail.com>
 * VK:    vk.com/technomindlp
 *        vk.com/hphp_convertation
 * 
 */

namespace ProgressBar;

class ProgressBar
{
    protected $maxCount;
    protected $length;
    protected $prefix;
    protected $postfix;
    protected $progressChar;
    protected $skeleton; // Скелет прогресс бара
    protected $exp; // Экспонента прогресса. Фактически - количество символов, на которое будет изменяться прогресс бар за 1 процент

    protected $length_function; // Функция проверки длины строки

    /**
     * Конструктор прогресс бара
     * 
     * @param double $maxCount - максимальное число операндов до достижения цели
     * @param int $length - длина прогресс бара (в символах, учитывается только активное поле символов)
     * @param mixed $prefix - префикс прогресс бара
     * @param mixed $postfix - постфикс прогресс бара
     * @param string $progressChar - символ, которым будет заполняться прогресс бар
     * 
     * @throws \Exception - выбрасывает исключения при аллогичных значениях параметров
     * 
     * После инициализации класса сразу же отрисовывается пустой прогресс бар
     * 
     * $prefix и $postfix могут быть строками или коллбэками. В качестве коллбэков они принимают аргументы:
     * 1. Текущая позиция
     * 2. Максимальная позиция
     * 
     * @example:
     * 
     * $progress = new \ProgressBar\ProgressBar (228, 25);
     * 
     * for ($i = 0; $i <= 228; ++$i)
     *     $progress->update ($i);
     * 
     * $progress->clear (); // Удаляем прогресс бар после отработки
     */
    public function __construct ($maxCount, $length, $prefix = '', $postfix = '', $progressChar = '█')
    {
        if (!is_numeric ($maxCount) || $maxCount < 0)
            throw new \Exception ('$maxCount param must be a non-negative number');

        if (!is_int ($length) || $length <= 0)
            throw new \Exception ('$length param must be an integer bigger than zero');

        if (!is_string ($progressChar))
            throw new \Exception ('$progressChar param must be an symbol');

        $this->maxCount        = $maxCount;
        $this->length          = $length;
        $this->prefix          = $prefix;
        $this->postfix         = $postfix;
        $this->progressChar    = $progressChar;

        $this->skeleton = (is_callable ($prefix) ?
            $prefix (0, $maxCount) : $prefix) .'0% |'.

            str_repeat (' ', $length) .'|'. 
            
            (is_callable ($postfix) ?
                $postfix (0, $maxCount) : $postfix);
        
        // Рекомендуется использовать mb_strlen для совместимости с юникод символами
        $this->length_function = extension_loaded ('mbstring') ? 'mb_strlen' : 'strlen';
        $this->exp = $length / 100;

        echo $this->skeleton;
    }

    /**
     * Обновление прогресс бара
     * 
     * @param double $position - позиция прогресс бара
     * 
     * @return float - возвращает процентное соотношение прогресс бара
     * 
     * @throws \Exception - выбрасывает исключения при неверных значениях параметра $position
     */
    public function update ($position): float
    {
        if ($position > $this->maxCount)
            throw new \Exception ('$position param mustn\'t be upper than $maxCount');

        if ($position < 0)
            throw new \Exception ('$position param must be upper than zero');

        $this->offset ($current_length = call_user_func ($this->length_function, $this->skeleton));

        $process = (int)($floatProcess = $position / $this->maxCount * 100);

        $this->skeleton = (is_callable ($this->prefix) ?
            call_user_func_array ($this->prefix, array ($position, $this->maxCount)) : $this->prefix) .
			
            str_repeat (' ', 3 - call_user_func ($this->length_function, $process)) . $process .'% |'.
			str_repeat ($this->progressChar, $processExp = $process * $this->exp) .
			str_repeat (' ', $this->length - $processExp) .'|'.
			
			(is_callable ($this->postfix) ?
				call_user_func_array ($this->postfix, array ($position, $this->maxCount)) : $this->postfix);

        echo $this->skeleton . (($length = call_user_func ($this->length_function, $this->skeleton)) < $current_length ?
            str_repeat (' ', $current_length - $length) .
            str_repeat (chr (8), $current_length - $length) : '');

        return $floatProcess;
    }

    /**
     * Очистка прогресс бара
     * Удаляет прогресс бар из консоли, заполняя его место пробелами и смещая указатель в начало бывшего прогресс бара
     */
    public function clear ()
    {
        $this->offset ($length = call_user_func ($this->length_function, $this->skeleton));

        echo str_repeat (' ', $length);

        $this->offset ($length);
    }

    /**
     * Смещение указателя на $length символов
     * 
     * @param int $length - количество символов для смещения
     */
    protected function offset ($length)
    {
        echo str_repeat (chr (8), (int) $length);
    }
}

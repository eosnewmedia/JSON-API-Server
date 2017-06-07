<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class SortInstruction
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var boolean
     */
    private $ascending;

    /**
     * @param string $instruction
     */
    public function __construct(string $instruction)
    {
        $this->field = $instruction;
        $this->ascending = true;

        if (strpos($instruction, '-') === 0) {
            $this->ascending = false;
            $this->field = substr($instruction, 1);
        }
    }

    /**
     * @return string
     */
    public function field(): string
    {
        return $this->field;
    }

    /**
     * @return boolean
     */
    public function ascending(): bool
    {
        return $this->ascending;
    }
}

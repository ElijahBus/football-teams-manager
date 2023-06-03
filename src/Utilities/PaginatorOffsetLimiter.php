<?php

namespace App\Utilities;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class PaginatorOffsetLimiter
{
    private int $offset = 5;

    public function setOffset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getResultRange(Request $request, callable $callback): array
    {
        $firstResultStart = 0;
        $paginatorPerPageItems = $this->offset;
        $maxPage = $this->offset;

        try {
            if ($request->get('max_page')) {
                $maxPage = $request->get('max_page');
            }

            $firstResultStart = $maxPage - $paginatorPerPageItems;

            return [$firstResultStart, $maxPage];
        } catch (Exception|\TypeError $e) {
            // LOG The Exception
            $maxPage = $this->offset;
            $callback();
        }

        return [$firstResultStart, $maxPage];
    }
}
<?php

namespace App\Services\Food;

use App\Models\Food;
use App\Models\FoodCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FoodService
{
    /**
     * Build the base query with filters.
     *
     * @param  array{search?:string|null, category?:string|int|null} $filters
     */
    public function buildIndexQuery(array $filters): Builder
    {
        $query = Food::query()->with('category');

        // Search (same as yours) …
        if (!empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            [$asDecimal, $numericRaw] = $this->extractNumeric($term);

            $query->where(function ($q) use ($term, $asDecimal, $numericRaw) {
                /** @var Builder $q */
                $q->where('name', 'like', "%{$term}%");
                if ($asDecimal !== null && $numericRaw !== null) {
                    $q->orWhere('price', $asDecimal)
                        ->orWhereRaw('CAST(price AS CHAR) LIKE ?', ["%{$numericRaw}%"]);
                }
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        // Sorting
        $allowed = ['name', 'price'];
        $sort = in_array($filters['sort'] ?? '', $allowed, true) ? $filters['sort'] : 'name';
        $direction = strtolower((string)($filters['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sort, $direction);

        return $query;
    }

    /**
     * Return foods + categories for the index page.
     *
     * @param  array{search?:string|null, category?:string|int|null} $filters
     * @return array{foods: Collection, categories: Collection}
     */
    public function getIndexData(array $filters): array
    {
        $foods = $this->buildIndexQuery($filters)
            ->paginate(5)
            ->withQueryString();

        $categories = FoodCategory::all();

        return compact('foods', 'categories');
    }
    /**
     * Pull a numeric part out of a free-text search term.
     * Returns [formattedDecimalForDB, rawNumericForLike] or [null, null] if none.
     *
     */
    private function extractNumeric(string $term): array
    {
        $numericRaw = preg_replace('/[^\d.,]/', '', $term) ?? '';
        $numericRaw = str_replace(',', '', $numericRaw);
        if ($numericRaw === '' || !is_numeric($numericRaw)) return [null, null];
        $asDecimal = number_format((float) $numericRaw, 2, '.', '');
        return [$asDecimal, $numericRaw];
    }
}

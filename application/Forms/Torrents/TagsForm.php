<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 4:37 PM
 */

declare(strict_types=1);

namespace App\Forms\Torrents;

use App\Forms\Traits\PaginationTrait;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class TagsForm extends AbstractValidator
{
    use PaginationTrait;

    public function __construct()
    {
        $this->setInput([
            'page' => 1, 'limit' => 100
        ]);
    }

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'search' => new Assert\Optional(new Assert\NotBlank()),
            'page' => new Assert\PositiveOrZero(),
            'limit' => new AcmeAssert\RangeInt(['min' =>  0, 'max' => 200])
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return [];
    }

    public function flush(): void
    {
        $pdo_where = [];

        if ($this->hasInput('search')) {
            $pdo_where[] = ['AND `tag` LIKE :tag', 'params' => ['tag' => '%' . $this->getInput('search') . '%']];
        }

        $count = container()->get('dbal')->prepare([
            ['SELECT COUNT(`id`) FROM tags WHERE 1=1 '],
            ...$pdo_where
        ])->fetchScalar();
        $this->setPaginationTotal($count);

        $this->setPaginationLimit($this->getInput('limit'));
        $this->setPaginationPage($this->getInput('page'));

        $data = container()->get('dbal')->prepare([
            ['SELECT * FROM tags WHERE 1=1 '],
            ...$pdo_where,
            ['ORDER BY `pinned`, `count` DESC, `id` '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->getPaginationOffset(), 'rows' => $this->getPaginationLimit()]],
        ])->fetchAll();
        $this->setPaginationData($data);
    }
}

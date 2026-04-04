<?php

declare(strict_types=1);

namespace Tests\Fake\Form;

use App\Core\Form;
use Tests\Fake\Http\FakeRequest;

final class FakeForm extends Form
{
    public function __construct(FakeRequest $request, private bool $valid)
    {
        parent::__construct($request, []);
    }

    public function validate(): ?string
    {
        return $this->valid ? null : 'Invalid form';
    }

    protected function oldFields(): array
    {
        return ['dummy'];
    }
}

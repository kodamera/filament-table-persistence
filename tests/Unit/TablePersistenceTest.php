<?php

declare(strict_types=1);

use Kodamera\FilamentTablePersistence\TablePersistence;
use Livewire\Component;

it('applies by default', function (): void {
    $component = new class extends Component
    {
        public function render(): string
        {
            return '<div></div>';
        }
    };

    expect(TablePersistence::appliesTo($component))->toBeTrue();
});

it('does not apply when disabled', function (): void {
    TablePersistence::configure(fn (TablePersistence $p): TablePersistence => $p->disabled());

    $component = new class extends Component
    {
        public function render(): string
        {
            return '<div></div>';
        }
    };

    expect(TablePersistence::appliesTo($component))->toBeFalse();
});

it('respects except list', function (): void {
    $component = new class extends Component
    {
        public function render(): string
        {
            return '<div></div>';
        }
    };

    TablePersistence::configure(fn (TablePersistence $p): TablePersistence => $p->except([$component::class]));

    expect(TablePersistence::appliesTo($component))->toBeFalse();
});

it('respects only list', function (): void {
    $included = new class extends Component
    {
        public function render(): string
        {
            return '<div></div>';
        }
    };

    $excluded = new class extends Component
    {
        public function render(): string
        {
            return '<div></div>';
        }
    };

    TablePersistence::configure(fn (TablePersistence $p): TablePersistence => $p->only([$included::class]));

    expect(TablePersistence::appliesTo($included))->toBeTrue()
        ->and(TablePersistence::appliesTo($excluded))->toBeFalse();
});

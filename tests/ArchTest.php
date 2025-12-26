<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('models extend Eloquent Model')
    ->expect('Yannelli\EntryVault\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('events use Dispatchable trait')
    ->expect('Yannelli\EntryVault\Events')
    ->toUseTrait('Illuminate\Foundation\Events\Dispatchable');

arch('states extend EntryState')
    ->expect('Yannelli\EntryVault\States')
    ->classes()
    ->toExtend('Yannelli\EntryVault\States\EntryState');

arch('transitions extend Transition')
    ->expect('Yannelli\EntryVault\Transitions')
    ->toExtend('Spatie\ModelStates\Transition');

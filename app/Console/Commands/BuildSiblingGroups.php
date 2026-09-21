<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\SiblingDiscountService;
use Illuminate\Console\Command;

class BuildSiblingGroups extends Command
{
    protected $signature = 'siblings:rebuild';
    protected $description = 'Group students by parent_phone and apply sibling discounts';

    public function handle()
    {
        // 1. Assign parent_group_key = normalized parent_phone
        Student::whereNull('parent_group_key')
            ->orWhere('parent_group_key', '')
            ->chunkById(100, function ($students) {
                foreach ($students as $s) {
                    $s->update(['parent_group_key' => $s->parent_phone]);
                }
            });

        $this->info('Group keys assigned.');

        // 2. Recompute discount for each group
        $count = SiblingDiscountService::recomputeAll();
        $this->info("Sibling discounts recomputed for {$count} students.");

        // 3. Report groups with siblings
        $groups = Student::whereNotNull('parent_group_key')
            ->selectRaw('parent_group_key, COUNT(*) as c')
            ->groupBy('parent_group_key')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $this->info("Groups with siblings: " . $groups->count());
        return 0;
    }
}

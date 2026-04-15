<?php

namespace Tests\Feature\Academics;

use App\Models\Programs;
use App\Models\Subjects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubjectMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_subjects_table_uses_id_as_primary_key(): void
    {
        $columns = DB::select("PRAGMA table_info('subjects')");

        $idColumn = collect($columns)->firstWhere('name', 'id');

        $this->assertNotNull($idColumn);
        $this->assertSame(1, (int) $idColumn->pk);
    }

    public function test_subjects_can_be_attached_to_programs_through_pivot_table(): void
    {
        $program = Programs::query()->create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'department' => 'Institute of Information Technology',
            'status' => 'active',
        ]);

        $subject = Subjects::query()->create([
            'subject_code' => 'CS105',
            'subject_name' => 'Database Management Systems',
            'units' => 3,
            'type' => 'lab',
            'status' => 'active',
        ]);

        $subject->programs()->attach($program->id, [
            'year_level' => 1,
            'semester' => '2',
            'school_year' => '2025-2026',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('program_subject', [
            'subject_id' => $subject->id,
            'program_id' => $program->id,
            'year_level' => 1,
            'semester' => '2',
            'school_year' => '2025-2026',
        ]);
    }
}

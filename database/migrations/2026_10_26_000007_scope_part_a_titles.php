<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Part A titles gain render scopes: plain-string titles become
     * { text, scopes } objects ticking every document, so existing
     * output is unchanged until the admin narrows a title. Parts B, C
     * and D keep plain-string titles and are untouched.
     */
    public function up(): void
    {
        $allScopes = ['swa', 'dtr', 'certification', 'cwa', 'payroll'];

        $heads = DB::table('signatories')->where('part', 'A')->get();

        foreach ($heads as $head) {
            $titles = json_decode($head->title, true);

            if (! is_array($titles)) {
                continue;
            }

            $changed = false;
            $converted = [];

            foreach ($titles as $title) {
                if (is_string($title)) {
                    $text = trim($title);

                    if ($text === '') {
                        $changed = true;
                        continue;
                    }

                    $changed = true;
                    $converted[] = ['text' => $text, 'scopes' => $allScopes];
                } else {
                    $converted[] = $title;
                }
            }

            if ($changed) {
                DB::table('signatories')
                    ->where('id', $head->id)
                    ->update(['title' => json_encode($converted)]);
            }
        }
    }

    public function down(): void
    {
        $heads = DB::table('signatories')->where('part', 'A')->get();

        foreach ($heads as $head) {
            $titles = json_decode($head->title, true);

            if (! is_array($titles)) {
                continue;
            }

            $changed = false;
            $converted = [];

            foreach ($titles as $title) {
                if (is_array($title)) {
                    $changed = true;
                    $converted[] = $title['text'] ?? '';
                } else {
                    $converted[] = $title;
                }
            }

            if ($changed) {
                DB::table('signatories')
                    ->where('id', $head->id)
                    ->update(['title' => json_encode($converted)]);
            }
        }
    }
};

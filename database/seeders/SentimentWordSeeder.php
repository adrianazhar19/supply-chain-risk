<?php

namespace Database\Seeders;

use App\Models\NegativeWord;
use App\Models\PositiveWord;
use Illuminate\Database\Seeder;

class SentimentWordSeeder extends Seeder
{
    /**
     * Seed the lexicon dictionaries used by the PHP-based sentiment analyzer.
     * Extend these lists as you tune the algorithm.
     */
    public function run(): void
    {
        $positive = [
            'growth', 'increase', 'profit', 'stable', 'improve', 'recovery',
            'surplus', 'expansion', 'boost', 'gain', 'rally', 'rebound',
        ];

        $negative = [
            'war', 'crisis', 'inflation', 'delay', 'disaster', 'conflict',
            'shortage', 'recession', 'default', 'sanction', 'tariff', 'strike',
        ];

        foreach ($positive as $word) {
            PositiveWord::firstOrCreate(['word' => $word]);
        }

        foreach ($negative as $word) {
            NegativeWord::firstOrCreate(['word' => $word]);
        }

        $this->command->info('SentimentWordSeeder: dictionary seeded.');
    }
}

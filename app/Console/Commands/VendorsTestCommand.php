<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use Illuminate\Console\Command;

class VendorsTestCommand extends Command
{
    protected $signature = 'vendors:test';

    protected $description = 'Command description';

    public function handle(): void
    {
        $searchTerm = 'IHSAAN FIHAARA 3';

        $results = Vendor::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
            ->orWhereRaw('JSON_SEARCH(LOWER(aliases), "one", LOWER(?)) IS NOT NULL', [$searchTerm])
            ->get();

        dd($results);
    }
}

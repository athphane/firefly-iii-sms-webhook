<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use App\Support\FireflyIII\Enums\AccountTypes;
use App\Support\FireflyIII\Facades\FireflyIII;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;

class SyncFireflyInstanceVendorsCommand extends Command
{
    protected $signature = 'firefly:sync-vendors';

    protected $description = 'Fetches all the Expense Accounts from your Firefly Instance and syncs them as Vendors in the application.';

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $this->handleTheThing();
    }

    /**
     * @throws ConnectionException
     */
    public function handleTheThing(): void
    {
        $accounts = FireflyIII::accounts(AccountTypes::EXPENSE, true);

        foreach ($accounts as $account) {
            $account_id = $account['id'];
            $attributes = $account['attributes'];
            $notes = $attributes['notes'] ?? null;
            if (str($notes)->contains('***
NOT A VENDOR
***')) {
                $this->line('Skipping account: ' . $attributes['name'] . ' as it is not a vendor.');
                continue;
            }

            $vendor = Vendor::where('firefly_account_id', $account_id)->first();

            if (!$vendor) {
                $vendor = new Vendor();
                $vendor->firefly_account_id = $account_id;
            }

            $vendor->name = $attributes['name'];
            $vendor->description = $attributes['description'] ?? null;
            $vendor->save();

            if ($vendor) {
                $this->syncVendorAliases($vendor, $attributes['notes'] ?? '');
            }
        }
    }

    /**
     * @throws ConnectionException
     */
    public function syncVendorAliases(Vendor $vendor, string $existing_notes): void
    {
        if ($vendor->aliases) {
            if (filled($existing_notes)) {
                $existing_aliases = str($existing_notes)
                    ->between("*START:ALIASES* \n", "\n*END:ALIASES*\n");

                if ($existing_aliases->isEmpty()) {
                    $notes = str($existing_notes)
                        ->prepend("*START:ALIASES* \n")
                        ->append(implode(",\n", Arr::flatten($vendor->aliases)))
                        ->append("\n*END:ALIASES*\n");
                } else {
                    $notes = str($existing_notes)
                        ->replace($existing_aliases, implode(",\n", Arr::flatten($vendor->aliases)))
                        ->append("\n*END:ALIASES*\n");
                }
            } else {
                $notes = str('')
                    ->prepend("*START:ALIASES* \n")
                    ->append(implode(",\n", Arr::flatten($vendor->aliases)))
                    ->append("\n*END:ALIASES*\n");
            }

            FireflyIII::updateAccount($vendor->firefly_account_id, [
                'name'  => $vendor->name,
                'notes' => $notes->toString(),
            ]);

            $this->info('Vendor aliases synced for: ' . $vendor->name);
        }
    }
}

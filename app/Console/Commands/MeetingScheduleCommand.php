<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Services\MeetingService;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MeetingScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meeting:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute scheduling when meeting deadline is reached';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now()->tz('Asia/Hong_Kong');
        $date = Carbon::parse($now)->toDateString();
        $time = Carbon::parse($now)->toTimeString();
        Meeting::where('status', '=', 'Pending')
            ->whereDate('deadline', '<', $date)
            ->orWhere(function ($query) use ($date, $time) {
                $query->where('status', '=', 'Pending')
                    ->whereDate('deadline', '=', $date)
                    ->whereTime('deadline', '<=', $time);
            })
            ->each(fn($meeting) => MeetingService::schedule($meeting));
        return Command::SUCCESS;
    }
}

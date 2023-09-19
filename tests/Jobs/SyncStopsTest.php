<?php

namespace Tests\Jobs;

use App\Enums\LogStatus;
use App\Jobs\SyncStopsJob;
use App\Models\Stop;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Webmozart\Assert\Assert;
use ZipArchive;

class SyncStopsTest extends TestCase
{
    /**
     * Test invalid key.
     */
    public function testInvalidKey(): void
    {
        // given
        config(['trafiklab.stops.key' => 'invalid']);
        $stop = Stop::factory()->createOne();
        Http::fake([]);
        $this->travelTo(now());

        // when
        $this->assertThrows(fn () => SyncStopsJob::dispatchSync());

        // then
        $response = Arr::get(Http::recorded(), '0.1');
        Assert::isInstanceOf($response, Response::class);
        static::assertTrue($response->forbidden());
        $this->assertDatabaseHas('stops', [
            'id' => $stop->id,
        ]);
        $this->assertDatabaseCount('stops', 1);
        $this->assertDatabaseHas('logs', [
            'holder_id' => null,
            'holder_type' => null,
            'job_type' => SyncStopsJob::class,
            'job_started_at' => now(),
            'status' => LogStatus::DownloadingStops,
        ]);
        $this->assertDatabaseMissing('logs', ['status' => LogStatus::ExtractingStops]);
    }

    /**
     * Test invalid response.
     */
    public function testInvalidResponse(): void
    {
        // given
        $stop = Stop::factory()->createOne();
        Http::fake();
        $this->travelTo(now());

        // when
        $this->assertThrows(fn () => SyncStopsJob::dispatchSync());

        // then
        $this->assertDatabaseHas('stops', [
            'id' => $stop->id,
        ]);
        $this->assertDatabaseCount('stops', 1);
        $this->assertDatabaseHas('logs', [
            'holder_id' => null,
            'holder_type' => null,
            'job_type' => SyncStopsJob::class,
            'job_started_at' => now(),
            'status' => LogStatus::ExtractingStops,
        ]);
        $this->assertDatabaseMissing('logs', ['status' => LogStatus::DeletingEvents]);
    }

    /**
     * Test completed.
     */
    public function testCompleted(): void
    {
        // given
        $stop = Stop::factory()->createOne();
        $filePath = tempnam(sys_get_temp_dir(), __CLASS__ . ':' . __METHOD__);
        Assert::string($filePath);
        $archive = new ZipArchive();
        $archive->open($filePath, ZipArchive::CREATE);
        $archive->addFromString(
            '_stops.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <PublicationDelivery>
                <dataObjects>
                    <SiteFrame version="20230919080536" id="SE:050:SiteFrame:1">
                        <stopPlaces>
                            <StopPlace version="20131219" id="SE:050:StopPlace:1">
                                <keyList>
                                    <KeyValue>
                                        <Key>rikshallplats</Key>
                                        <Value>740000001</Value>
                                    </KeyValue>
                                </keyList>
                                <Name>Stockholm Centralstation</Name>
                            </StopPlace>
                        </stopPlaces>
                    </SiteFrame>
                </dataObjects>
            </PublicationDelivery>'
        );
        $archive->close();
        $archiveContent = file_get_contents($filePath);
        Assert::string($archiveContent);
        Http::fake(['*' => Http::response($archiveContent)]);
        $this->travelTo(now());

        // when
        SyncStopsJob::dispatchSync();

        // then
        $this->assertDatabaseMissing('stops', [
            'id' => $stop->id,
        ]);
        $this->assertDatabaseHas('stops', [
            'name' => 'Stockholm Centralstation',
            'code' => '740000001',
        ]);
        $this->assertDatabaseHas('logs', [
            'holder_id' => null,
            'holder_type' => null,
            'job_type' => SyncStopsJob::class,
            'job_started_at' => now(),
            'status' => LogStatus::Completed,
        ]);
    }
}

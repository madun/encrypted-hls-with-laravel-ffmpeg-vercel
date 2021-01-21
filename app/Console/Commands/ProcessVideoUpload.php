<?php

namespace App\Console\Commands;

use FFMpeg\Format\Video\X264;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSVideoFilters;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSExporter;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ProcessVideoUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video-upload:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert the uploaded video into HLS.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $r_144p  = (new X264('aac'))->setKiloBitrate(95);
        // $r_240p  = (new X264('aac'))->setKiloBitrate(150);
        $r_360p  = (new X264('aac'))->setKiloBitrate(276);
        $r_480p  = (new X264('aac'))->setKiloBitrate(750);
        $r_720p  = (new X264('aac'))->setKiloBitrate(2048);
        // $r_1080p = (new X264('aac'))->setKiloBitrate(4096);
        // $r_2k    = (new X264('aac'))->setKiloBitrate(6144);
        // $r_4k    = (new X264('aac'))->setKiloBitrate(17408);

        $this->info('Converting redfield.mp4');

        $key = HLSExporter::generateEncryptionKey();
        $videoFilename = 'redfield';

        FFMpeg::fromDisk('uploads')
            ->open("{$videoFilename}.mp4")
            ->exportForHLS()
            ->withRotatingEncryptionKey(function($filename, $contents) use ($videoFilename){
                Storage::disk('public')->put("videos/{$videoFilename}/{$filename}", $contents);
            })
            // ->addFormat($r_144p, function (HLSVideoFilters $filters) {
            //     $filters->resize(256, 144);
            // })
            // ->addFormat($r_240p, function (HLSVideoFilters $filters) {
            //     $filters->resize(426, 240);
            // })
            ->addFormat($r_360p, function (HLSVideoFilters $filters) {
                $filters->resize(640, 360);
            })
            ->addFormat($r_480p, function (HLSVideoFilters $filters) {
                $filters->resize(854, 480);
            })
            ->addFormat($r_720p, function (HLSVideoFilters $filters) {
                $filters->resize(1280, 720);
            })
            // ->addFormat($r_1080p, function (HLSVideoFilters $filters) {
            //     $filters->resize(1920, 1080);
            // })
            // ->addFormat($r_2k, function (HLSVideoFilters $filters) {
            //     $filters->resize(2560, 1440);
            // })
            // ->addFormat($r_4k, function (HLSVideoFilters $filters) {
            //     $filters->resize(3840, 2160);
            // })
            ->onProgress(function ($progress) {
                $this->info("Progress: {$progress}%");
            })
            ->toDisk('public')
            ->save("videos/{$videoFilename}/{$videoFilename}.m3u8");

        $this->info('Done!');
    }
}

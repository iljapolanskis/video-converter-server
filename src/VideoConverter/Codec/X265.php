<?php

namespace App\VideoConverter\Codec;

use FFMpeg\Format\Video\DefaultVideo;

class X265 extends DefaultVideo
{
    private bool $bframesSupport = true;
    private int $passes = 2;
    protected $modulus = 2;

    public function __construct($audioCodec = 'aac', $videoCodec = 'libx265')
    {
        $this
            ->setAudioCodec($audioCodec)
            ->setVideoCodec($videoCodec);
    }

    /**
     * {@inheritDoc}
     */
    public function supportBFrames(): bool
    {
        return $this->bframesSupport;
    }

    /**
     * @param $support
     *
     * @return X265
     */
    public function setBFramesSupport($support): self
    {
        $this->bframesSupport = $support;

        return $this;
    }

    public function getAvailableAudioCodecs(): array
    {
        return ['copy', 'aac', 'libvo_aacenc', 'libfaac', 'libmp3lame', 'libfdk_aac'];
    }

    public function getAvailableVideoCodecs(): array
    {
        return ['libx265'];
    }

    public function setPasses(int $passes): self
    {
        $this->passes = $passes;
        return $this;
    }

    public function getPasses(): int
    {
        return $this->passes;
    }
}

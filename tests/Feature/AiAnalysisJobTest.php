<?php

test('the ai analysis job class is autoloadable', function () {
    expect(class_exists(\App\Jobs\AIAnalysisJob::class))->toBeTrue();
});

<?php

// hardcoded https
if (in_array(config('app.env'), ['stage', 'production'], true)) {
    \URL::forceScheme('https'); // TODO: find more appropriate solution to force https for these assets
}

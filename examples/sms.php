<?php

\mailjet()->sendSMS(
    '+491234567890', // from
    '+499876543210', // to
    'This is a "test". 🕷'
);

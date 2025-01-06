<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'        => 'Das Feld :attribute muss akzeptiert werden.',
    'accepted_if'     => 'Das Feld :attribute muss akzeptiert werden, wenn :other :value ist.',
    'active_url'      => ':attribute ist keine gültige URL.',
    'after'           => ':attribute muss ein Datum nach :date sein.',
    'after_or_equal'  => ':attribute muss ein Datum nach oder gleich :date sein.',
    'alpha'           => ':attribute darf nur Buchstaben enthalten.',
    'alpha_dash'      => ':attribute darf nur Buchstaben, Zahlen und Bindestriche enthalten.',
    'alpha_num'       => ':attribute darf nur Buchstaben und Zahlen enthalten',
    'array'           => ':attribute muss ein Array sein.',
    'ascii'           => 'Das Feld :attribute darf nur alphanumerische Einzelbyte-Zeichen und Symbole enthalten.',
    'before'          => ':attribute muss ein Datum vor :date sein.',
    'before_or_equal' => ':attribute muss ein Datum vor oder gleich :date sein.',
    'between'         => [
        'array'   => ':attribute muss zwischen :min und :max Elemente haben.',
        'file'    => ':attribute muss zwischen :min und :max Kilobyte haben.',
        'numeric' => ':attribute muss zwischen :min und :max sein.',
        'string'  => ':attribute muss zwischen :min und :max Zeichen haben.',
    ],
    'boolean'           => ':attribute Feld muss Wahr oder Falsch sein.',
    'can'               => 'Das Feld :attribute enthält einen unberechtigten Wert.',
    'confirmed'         => ':attribute Bestätigung stimmt nicht überein.',
    'current_password'  => 'Das eingegebene, alte Passwort ist nicht korrekt.',
    'date'              => ':attribute ist kein gültiges Datum.',
    'date_equals'       => 'Das Feld :attribute muss ein Datum sein, das dem :date entspricht.',
    'date_format'       => ':attribute entspricht nicht dem Format :format.',
    'decimal'           => 'Das Feld :attribute muss :decimal Dezimalstellen haben.',
    'declined'          => 'Das Feld :attribute muss abgelehnt werden.',
    'declined_if'       => 'Das Feld :attribute muss abgelehnt werden, wenn :other :value ist.',
    'different'         => ':attribute und :other müssen unterschiedlich sein.',
    'digits'            => ':attribute muss :digits Stellen haben.',
    'digits_between'    => ':attribute muss zwischen :min und :max Stellen haben.',
    'dimensions'        => ':attribute hat eine ungültige Bildgröße.',
    'distinct'          => ':attribute Feld enthält doppelten Wert.',
    'doesnt_end_with'   => 'Das Feld :attribute darf nicht mit einer der folgenden Angaben enden: :values.',
    'doesnt_start_with' => 'Das Feld :attribute darf nicht mit einer der folgenden Angaben beginnen: :values.',
    'email'             => ':attribute muss eine gültige E-Mail Adresse beinhalten.',
    'ends_with'         => 'Das Feld :attribute muss mit einer der folgenden Angaben enden: :values.',
    'enum'              => 'Auswahl :attribute ist ungültig.',
    'exists'            => 'Auswahl :attribute ist ungültig.',
    'file'              => ':attribute muss eine Datei sein.',
    'filled'            => ':attribute Feld muss einen Wert enthalten.',
    'gt'                => [
        'array'   => 'Das :attribute Feld muss mehr als :value Elemente beinhalten.',
        'file'    => 'Das Feld :attribute muss größer sein als :value Kilobytes.',
        'numeric' => 'Das Feld :attribute muss größer sein als :value.',
        'string'  => 'Das Feld :attribute muss größer sein als :value Zeichen.',
    ],
    'gte' => [
        'array'   => 'Das Feld :attribute muss mindestens :value Elemente enthalten.',
        'file'    => 'Das Feld :attribute muss größer oder gleich :value Kilobytes sein.',
        'numeric' => 'Das Feld :attribute muss größer oder gleich :value sein.',
        'string'  => 'Das Feld :attribute muss größer oder gleich :value Zeichen lang sein.',
    ],
    'image'     => ':attribute muss ein Bild sein.',
    'in'        => 'Auswahl :attribute ist ungültig.',
    'in_array'  => ':attribute Feld existiert nicht unter :other.',
    'integer'   => ':attribute muss eine ganze Zahl sein.',
    'ip'        => ':attribute muss eine gültige IP Adresse sein.',
    'ipv4'      => ':attribute muss eine gültige IPv4 Adresse haben.',
    'ipv6'      => ':attribute muss eine gültige IPv6 Adresse haben.',
    'json'      => ':attribute muss ein gültiger JSON String (Zeichenkette) sein.',
    'lowercase' => 'Das Feld :attribute muss klein geschrieben werden.',
    'lt'        => [
        'array'   => 'Das Feld :attribute muss weniger Elemente als :value enthalten.',
        'file'    => 'Das Feld :attribute muss kleiner sein als :value Kilobytes.',
        'numeric' => 'Das Feld :attribute muss kleiner als :value sein.',
        'string'  => 'Das Feld :attribute muss kürzer als :value Zeichen sein.',
    ],
    'lte' => [
        'array'   => 'Das Feld :attribute darf nicht mehr als :value Elemente enthalten.',
        'file'    => 'Das Feld :attribute muss kleiner oder gleich :value Kilobytes sein.',
        'numeric' => 'Das Feld :attribute muss kleiner oder gleich :value sein.',
        'string'  => 'Das Feld :attribute muss kleiner oder gleich :value Zeichen lang sein.',
    ],
    'mac_address' => 'Das Feld :attribute muss eine gültige MAC-Adresse sein.',
    'max'         => [
        'array'   => ':attribute darf nicht mehr als :max Elemente haben.',
        'file'    => ':attribute darf nicht größer sein als :max Kilobyte.',
        'numeric' => ':attribute darf nicht größer sein als :max.',
        'string'  => ':attribute darf nicht mehr als :max Zeichen haben.',
    ],
    'max_digits' => 'Das Feld :attribute darf nicht mehr als :max Ziffern enthalten.',
    'mimes'      => ':attribute muss diesem Dateityp entsprechen: :values.',
    'mimetypes'  => ':attribute muss diesem Dateityp entsprechen: :values.',
    'min'        => [
        'array'   => ':attribute muss mindestens :min Elemente enthalten.',
        'file'    => ':attribute muss mindestens :min Kilobyte haben.',
        'numeric' => ':attribute muss mindestens :min.',
        'string'  => ':attribute muss mindestens :min Zeichen enthalten.',
    ],
    'min_digits'       => 'Das Feld :attribute muss mindestens :min Ziffern enthalten.',
    'missing'          => 'Das Feld :attribute darf nicht vorhanden sein.',
    'missing_if'       => 'Das Feld :attribute muss fehlen, wenn :other :value ist.',
    'missing_unless'   => 'Das Feld :attribute muss fehlen, es sei denn, :other ist :value.',
    'missing_with'     => 'Das Feld :attribute muss fehlen, wenn :values vorhanden ist.',
    'missing_with_all' => 'Das Feld :attribute muss fehlen, wenn :values vorhanden sind.',
    'multiple_of'      => 'Das Feld :attribute muss ein Vielfaches von :value sein.',
    'not_in'           => 'Auswahl :attribute ist ungültig.',
    'not_regex'        => ':attribute Format ist ungültig.',
    'numeric'          => ':attribute muss eine Zahl sein.',
    'password'         => [
        'letters'       => 'Das Feld :attribute muss mindestens einen Buchstaben enthalten.',
        'mixed'         => 'Das Feld :attribute muss mindestens einen Großbuchstaben und einen Kleinbuchstaben enthalten.',
        'numbers'       => 'Das Feld :attribute muss mindestens eine Zahl enthalten.',
        'symbols'       => 'Das Feld :attribute muss mindestens ein Symbol enthalten.',
        'uncompromised' => 'Das angegebene :attribute ist in einem Datenleck aufgetaucht. Bitte wähle ein anderes :attribute.',
    ],
    'present'              => ':attribute Feld muss vorhanden sein.',
    'prohibited'           => 'Das Feld :attribute ist verboten.',
    'prohibited_if'        => 'Das Feld :attribute ist verboten, wenn :other :value ist.',
    'prohibited_unless'    => 'Das Feld :attribute ist verboten, wenn :other nicht in :values enthalten ist.',
    'prohibits'            => 'Das Feld :attribute verbietet das Vorhandensein von :other.',
    'regex'                => ':attribute Format ist ungültig.',
    'required'             => ':attribute Feld ist notwendig.',
    'required_array_keys'  => 'Das Feld :attribute muss Einträge enthalten für: :values.',
    'required_if'          => ':attribute Feld ist notwendig, wenn :other :value entspricht.',
    'required_if_accepted' => 'Das Feld :attribute ist erforderlich, wenn :other akzeptiert wird.',
    'required_unless'      => ':attribute Feld ist notwendig, außer wenn :other :values entspricht.',
    'required_with'        => ':attribute Feld ist notwendig, wenn :values vorhanden ist.',
    'required_with_all'    => ':attribute Feld ist notwendig, wenn :values vorhanden ist.',
    'required_without'     => ':attribute Feld ist notwendig, wenn :values nicht vorhanden ist.',
    'required_without_all' => ':attribute Feld ist notwendig, wenn :values nicht vorhanden sind.',
    'same'                 => ':attribute und :other müssen übereinstimmen.',
    'size'                 => [
        'array'   => ':attribute muss :size Elemente enthalten.',
        'file'    => ':attribute muss :size Kilobyte haben.',
        'numeric' => ':attribute muss :size sein.',
        'string'  => ':attribute muss :size Zeichen haben.',
    ],
    'starts_with' => 'Das Feld :attribute muss mit einer der folgenden Angaben beginnen: :values.',
    'string'      => ':attribute muss ein String (Zeichenkette) sein.',
    'timezone'    => ':attribute muss eine gültige Zone enthalten.',
    'unique'      => ':attribute wurde bereits verwendet.',
    'uploaded'    => ':attribute konnte nicht hochgeladen werden.',
    'uppercase'   => 'Das Feld :attribute muss in Großbuchstaben geschrieben werden.',
    'url'         => ':attribute hat ein ungültiges Format.',
    'ulid'        => 'Das Feld :attribute muss eine gültige ULID sein.',
    'uuid'        => 'Das Feld :attribute muss eine gültige UUID sein.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'password' => 'Passwort',
        // todo move it out of here
        // Notification type
        'notification' => [
            'icon' => [
                'missing' => 'Das Symbol konnte nicht gefunden werden.',
                'size'    => 'Das Symbol darf nicht größer als :size sein. Bitte <a href=":link">besuche diese Seite</a> um das Bild zu komprimieren. Stelle sicher, dass das Bild anschließend kleiner als :size ist.',
                'type'    => 'Das Symbol darf nur dem folgenden Typ entsprechen: :type.',
            ],
            'translations' => 'Fehlende Übersetzung',
            'link'         => 'Fehlender Link'
        ],
        'ingestion' => [
            'not_allowed' => 'You cannot replace recipes for this mealtime'
        ]
    ],

];

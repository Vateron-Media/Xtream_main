<?php

class E3223A8ad822526d8F69418863b6E8B5 {
    public $validEpg = false;
    public $epgSource;
    public $from_cache = false;
    function __construct($F3803fa85b38b65447e6d438f8e9176a, $F7b03a1f7467c01c6ea18452d9a5202f = false) {
        $this->eCe97C9Fe9A866e5B522e80E43B30997($F3803fa85b38b65447e6d438f8e9176a, $F7b03a1f7467c01c6ea18452d9a5202f);
    }
    public function a53d17AB9BD15890715e7947C1766953() {
        $output = array();
        foreach ($this->epgSource->channel as $d76067cf9572f7a6691c85c12faf2a29) {
            $e818ebc908da0ee69f4f99daba6a1a18 = trim((string) $d76067cf9572f7a6691c85c12faf2a29->attributes()->id);
            $cfd246a8499e5bb4a9d89e37c524322a = !empty($d76067cf9572f7a6691c85c12faf2a29->{"display-name"}) ? trim((string) $d76067cf9572f7a6691c85c12faf2a29->{"display-name"}) : '';
            if (!array_key_exists($e818ebc908da0ee69f4f99daba6a1a18, $output)) {
                $output[$e818ebc908da0ee69f4f99daba6a1a18] = array();
                $output[$e818ebc908da0ee69f4f99daba6a1a18]["display_name"] = $cfd246a8499e5bb4a9d89e37c524322a;
                $output[$e818ebc908da0ee69f4f99daba6a1a18]["langs"] = array();
                goto f437d78f880d472d4855ee4a79e88738;
            }
            f437d78f880d472d4855ee4a79e88738:
        }
        foreach ($this->epgSource->programme as $d76067cf9572f7a6691c85c12faf2a29) {
            $e818ebc908da0ee69f4f99daba6a1a18 = trim((string) $d76067cf9572f7a6691c85c12faf2a29->attributes()->channel);
            if (array_key_exists($e818ebc908da0ee69f4f99daba6a1a18, $output)) {
                $b798ef834bcdc73cfeb4e4e0309db68d = $d76067cf9572f7a6691c85c12faf2a29->title;
                foreach ($b798ef834bcdc73cfeb4e4e0309db68d as $E4416ae8f96620daee43ac43f9515200) {
                    $lang = (string) $E4416ae8f96620daee43ac43f9515200->attributes()->lang;
                    if (in_array($lang, $output[$e818ebc908da0ee69f4f99daba6a1a18]["langs"])) {
                        goto B9a591c74e1269778d75306c044ebcfd;
                    }
                    $output[$e818ebc908da0ee69f4f99daba6a1a18]["langs"][] = $lang;
                    B9a591c74e1269778d75306c044ebcfd:
                }
                goto Efc4e285c9401f06e2cf53b247bfe3de;
            }
            Efc4e285c9401f06e2cf53b247bfe3de:
        }
        return $output;
    }
    public function a0b90401c3241088846A84F33c2B50fF($E2b08d0d6a74fb4e054587ee7c572a9f, $dfc6b62ce4c2bd11aeb45ae2e9441819) {
        global $f566700a43ee8e1f0412fe10fbdf03df;
        $f8f0da104ec866e0d96947b27214d28a = array();
        foreach ($this->epgSource->programme as $d76067cf9572f7a6691c85c12faf2a29) {
            $e818ebc908da0ee69f4f99daba6a1a18 = (string) $d76067cf9572f7a6691c85c12faf2a29->attributes()->channel;
            if (array_key_exists($e818ebc908da0ee69f4f99daba6a1a18, $dfc6b62ce4c2bd11aeb45ae2e9441819)) {
                $ff153ef1378baba89ae1f33db3ad14bf = $Fe7c1055293ad23ed4b69b91fd845cac = '';
                $start = strtotime(strval($d76067cf9572f7a6691c85c12faf2a29->attributes()->start));
                $stop = strtotime(strval($d76067cf9572f7a6691c85c12faf2a29->attributes()->stop));
                if (!empty($d76067cf9572f7a6691c85c12faf2a29->title)) {
                    $b798ef834bcdc73cfeb4e4e0309db68d = $d76067cf9572f7a6691c85c12faf2a29->title;
                    if (is_object($b798ef834bcdc73cfeb4e4e0309db68d)) {
                        $A2b796e1bb70296d4bed8ce34ce5691b = false;
                        foreach ($b798ef834bcdc73cfeb4e4e0309db68d as $E4416ae8f96620daee43ac43f9515200) {
                            if (!($E4416ae8f96620daee43ac43f9515200->attributes()->lang == $dfc6b62ce4c2bd11aeb45ae2e9441819[$e818ebc908da0ee69f4f99daba6a1a18]["epg_lang"])) {
                            }
                            $A2b796e1bb70296d4bed8ce34ce5691b = true;
                            $ff153ef1378baba89ae1f33db3ad14bf = base64_encode($E4416ae8f96620daee43ac43f9515200);
                            goto e4c96e08633c7941a558a5827643fdaf;
                        }
                        e4c96e08633c7941a558a5827643fdaf:
                        if ($A2b796e1bb70296d4bed8ce34ce5691b) {
                            goto e4a8b6d7fb0b14b9c6b0eb185abd70c1;
                        }
                        $ff153ef1378baba89ae1f33db3ad14bf = base64_encode($b798ef834bcdc73cfeb4e4e0309db68d[0]);
                        e4a8b6d7fb0b14b9c6b0eb185abd70c1:
                        goto b6563f7b5dc28cf2f7e3e01acffdd848;
                    }
                    $ff153ef1378baba89ae1f33db3ad14bf = base64_encode($b798ef834bcdc73cfeb4e4e0309db68d);
                    b6563f7b5dc28cf2f7e3e01acffdd848:
                    if (empty($d76067cf9572f7a6691c85c12faf2a29->desc)) {
                        goto E41be4030cad9418c6c0715ff44092c8;
                    }
                    $d1294148eb5638fe195478093cd6b93b = $d76067cf9572f7a6691c85c12faf2a29->desc;
                    if (is_object($d1294148eb5638fe195478093cd6b93b)) {
                        $A2b796e1bb70296d4bed8ce34ce5691b = false;
                        foreach ($d1294148eb5638fe195478093cd6b93b as $d4c3c80b508f5d00d05316e7aa0858de) {
                            if (!($d4c3c80b508f5d00d05316e7aa0858de->attributes()->lang == $dfc6b62ce4c2bd11aeb45ae2e9441819[$e818ebc908da0ee69f4f99daba6a1a18]["epg_lang"])) {
                            }
                            $A2b796e1bb70296d4bed8ce34ce5691b = true;
                            $Fe7c1055293ad23ed4b69b91fd845cac = base64_encode($d4c3c80b508f5d00d05316e7aa0858de);
                            goto b38f4203696ed62cf1fa4a722b1d2896;
                        }
                        b38f4203696ed62cf1fa4a722b1d2896:
                        if ($A2b796e1bb70296d4bed8ce34ce5691b) {
                            goto Ee1b837454608384e5e35a33c212f868;
                        }
                        $Fe7c1055293ad23ed4b69b91fd845cac = base64_encode($d1294148eb5638fe195478093cd6b93b[0]);
                        Ee1b837454608384e5e35a33c212f868:
                        goto C4cbcb36860182ce8d50e99ff7579bc2;
                    }
                    $Fe7c1055293ad23ed4b69b91fd845cac = base64_encode($d76067cf9572f7a6691c85c12faf2a29->desc);
                    C4cbcb36860182ce8d50e99ff7579bc2:
                    E41be4030cad9418c6c0715ff44092c8:
                    $e818ebc908da0ee69f4f99daba6a1a18 = addslashes($e818ebc908da0ee69f4f99daba6a1a18);
                    $dfc6b62ce4c2bd11aeb45ae2e9441819[$e818ebc908da0ee69f4f99daba6a1a18]["epg_lang"] = addslashes($dfc6b62ce4c2bd11aeb45ae2e9441819[$e818ebc908da0ee69f4f99daba6a1a18]["epg_lang"]);
                    $A73d5129dfb465fd94f3e09e9b179de0 = date("Y-m-d H:i:s", $start);
                    $cdd6af41b10abec2ff03fe043f3df1cf = date("Y-m-d H:i:s", $stop);
                    $f8f0da104ec866e0d96947b27214d28a[] = "('" . $f566700a43ee8e1f0412fe10fbdf03df->escape($E2b08d0d6a74fb4e054587ee7c572a9f) . "', '" . $f566700a43ee8e1f0412fe10fbdf03df->escape($e818ebc908da0ee69f4f99daba6a1a18) . "', '" . $f566700a43ee8e1f0412fe10fbdf03df->escape($A73d5129dfb465fd94f3e09e9b179de0) . "', '" . $f566700a43ee8e1f0412fe10fbdf03df->escape($cdd6af41b10abec2ff03fe043f3df1cf) . "', '" . $f566700a43ee8e1f0412fe10fbdf03df->escape($dfc6b62ce4c2bd11aeb45ae2e9441819[$e818ebc908da0ee69f4f99daba6a1a18]["epg_lang"]) . "', '" . $f566700a43ee8e1f0412fe10fbdf03df->escape($ff153ef1378baba89ae1f33db3ad14bf) . "', '" . $f566700a43ee8e1f0412fe10fbdf03df->escape($Fe7c1055293ad23ed4b69b91fd845cac) . "')";
                    goto F9a5fdab92aaa38ae7ad3cc982a3953e;
                }
                goto E45bcbb8d283399c92d22750351d1ab6;
            }
            F9a5fdab92aaa38ae7ad3cc982a3953e:
            E45bcbb8d283399c92d22750351d1ab6:
        }
        return !empty($f8f0da104ec866e0d96947b27214d28a) ? $f8f0da104ec866e0d96947b27214d28a : false;
    }
    public function ece97c9FE9a866e5B522E80e43b30997($F3803fa85b38b65447e6d438f8e9176a, $F7b03a1f7467c01c6ea18452d9a5202f) {
        $F1350a5569e4b73d2f9cb26483f2a0c1 = pathinfo($F3803fa85b38b65447e6d438f8e9176a, PATHINFO_EXTENSION);
        if ($F1350a5569e4b73d2f9cb26483f2a0c1 == "gz") {
            $d31de515789f8101b06d8ca646ef5e24 = gzdecode(file_get_contents($F3803fa85b38b65447e6d438f8e9176a));
            $a41f6a5b2ce6655f27b7747349ad1f33 = simplexml_load_string($d31de515789f8101b06d8ca646ef5e24, "SimpleXMLElement", "LIBXML_SO_SEKUGE");
            goto a36ffe2c42f7fe1e92fe8e9145a803a6;
        }
        if ($F1350a5569e4b73d2f9cb26483f2a0c1 == "xz") {
            $d31de515789f8101b06d8ca646ef5e24 = shell_exec("wget -qO- \"{$F3803fa85b38b65447e6d438f8e9176a}\" | unxz -c");
            $a41f6a5b2ce6655f27b7747349ad1f33 = simplexml_load_string($d31de515789f8101b06d8ca646ef5e24, "SimpleXMLElement", "LIBXML_SO_SEKUGE");
            goto f44deb54c5db953550f5d75def9f9644;
        }
        $d31de515789f8101b06d8ca646ef5e24 = file_get_contents($F3803fa85b38b65447e6d438f8e9176a);
        $a41f6a5b2ce6655f27b7747349ad1f33 = simplexml_load_string($d31de515789f8101b06d8ca646ef5e24, "SimpleXMLElement", "LIBXML_SO_SEKUGE");
        f44deb54c5db953550f5d75def9f9644:
        a36ffe2c42f7fe1e92fe8e9145a803a6:
        if ($a41f6a5b2ce6655f27b7747349ad1f33 !== false) {
            $this->epgSource = $a41f6a5b2ce6655f27b7747349ad1f33;
            if (empty($this->epgSource->programme)) {
                A78bf8D35765BE2408C50712cE7a43aD::E501281ad19aF8A4BBbf9BED91Ee9299("Not A Valid EPG Source Specified or EPG Crashed: " . $F3803fa85b38b65447e6d438f8e9176a);
                goto d12d5faf782b341065f9b12c6f8b93d8;
            }
            $this->validEpg = true;
            d12d5faf782b341065f9b12c6f8b93d8:
            goto a39013fbe338351a107efd61ec4e976b;
        }
        a78bF8D35765Be2408C50712cE7a43aD::e501281AD19AF8a4BBbF9BED91EE9299("No XML Found At: " . $F3803fa85b38b65447e6d438f8e9176a);
        a39013fbe338351a107efd61ec4e976b:
        $a41f6a5b2ce6655f27b7747349ad1f33 = $d31de515789f8101b06d8ca646ef5e24 = null;
    }
}

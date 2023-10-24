<?php

class ipTV_db {
    public $result;
    var $last_query;
    protected $dbuser;
    protected $dbpassword;
    protected $dbname;
    protected $dbhost;
    public $dbh;
    protected $pconnect = false;
    protected $connected = false;
    function __construct($e52446c69000e32dcba3587971751c55, $fe0e191d9679e3b25f12894b8643f8dc, $b4096134eae1a66dde5826015002e9e8, $e81fa4916966385db57a281e4350f3af, $Bbd935a3f7113263ed95731f6a48e3a4 = 7999, $f27e9801e97df9300aad11d5c40edeeb = false, $f828fbd7943068a1cd53cba5fe86120c = false) {
        $this->dbh = false;
        if ($f828fbd7943068a1cd53cba5fe86120c) {
            goto e13de7cdcb920c2abf50d5cbc49ba564;
        }
        $this->dbuser = $e52446c69000e32dcba3587971751c55;
        $this->dbpassword = $fe0e191d9679e3b25f12894b8643f8dc;
        $this->dbname = $b4096134eae1a66dde5826015002e9e8;
        $this->dbhost = $e81fa4916966385db57a281e4350f3af;
        $this->pconnect = $f27e9801e97df9300aad11d5c40edeeb;
        $this->dbport = $Bbd935a3f7113263ed95731f6a48e3a4;
        e13de7cdcb920c2abf50d5cbc49ba564:
    }
    function Ca531F7bdc43b966dEFB4ABA3C8fAf22() {
        if (!($this->connected && !$this->pconnect)) {
            goto d73ba8e709ebbde617732fce29c1c938;
        }
        $this->connected = false;
        $this->dbh->close();
        d73ba8e709ebbde617732fce29c1c938:
        return true;
    }
    function __destruct() {
        $this->Ca531F7bDC43B966DEFB4aba3c8faf22();
    }
    function cC637bCB0b74B82bEbc2776607e73BED() {
        if (!($this->connected && $this->dbh)) {
            $this->dbh = mysqli_init();
            $this->dbh->options(MYSQLI_OPT_CONNECT_TIMEOUT, 4);
            if ($this->pconnect) {
                $this->dbh->real_connect("p:" . $this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname, $this->dbport);
                goto fc98ddb734dd6642c38ce1a8fcd1b2db;
            }
            $this->dbh->real_connect($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname, $this->dbport);
            fc98ddb734dd6642c38ce1a8fcd1b2db:
            if (empty($this->dbh->error)) {
                $this->connected = true;
                mysqli_set_charset($this->dbh, "utf8");
                return true;
            }
            die(json_encode(array("error" => "MySQL: " . $this->dbh->error)));
        }
        return true;
    }
    function query($b0f1eb357ed72245e03dfe6268912497, $B0fb8cd24a354bd4e2e52cfd27accdb1 = false) {
        $this->CC637bcB0b74B82bEBc2776607e73beD();
        $bf71d30c152dd2edcc2dbd2d67b71257 = func_num_args();
        $bcde8f5075cc50fc02f07923570c4006 = func_get_args();
        $e3d665ac9d68febd9e8a2fa23a73999a = array();
        $C48e0083a9caa391609a3c645a2ec889 = 1;
        A527e2e94fe176728f169d39a4a4d85e:
        if (!($C48e0083a9caa391609a3c645a2ec889 < $bf71d30c152dd2edcc2dbd2d67b71257)) {
            $b0f1eb357ed72245e03dfe6268912497 = vsprintf($b0f1eb357ed72245e03dfe6268912497, $e3d665ac9d68febd9e8a2fa23a73999a);
            $this->last_query = $b0f1eb357ed72245e03dfe6268912497;
            if ($B0fb8cd24a354bd4e2e52cfd27accdb1 === true) {
                $this->result = mysqli_query($this->dbh, $b0f1eb357ed72245e03dfe6268912497, MYSQLI_USE_RESULT);
                goto b3c814d9a9c8c75b31bf0d7b00836252;
            }
            $this->result = mysqli_query($this->dbh, $b0f1eb357ed72245e03dfe6268912497);
            b3c814d9a9c8c75b31bf0d7b00836252:
            if ($this->result) {
                return true;
            }
            a78Bf8D35765bE2408c50712CE7A43aD::E501281aD19Af8A4bBbf9BEd91ee9299("MySQL Query Failed [" . $b0f1eb357ed72245e03dfe6268912497 . "]: " . mysqli_error($this->dbh));
            return false;
        }
        $e3d665ac9d68febd9e8a2fa23a73999a[] = mysqli_real_escape_string($this->dbh, $bcde8f5075cc50fc02f07923570c4006[$C48e0083a9caa391609a3c645a2ec889]);
        $C48e0083a9caa391609a3c645a2ec889++;
        goto A527e2e94fe176728f169d39a4a4d85e;
    }
    function c126fd559932F625CDf6098D86C63880($E4cee08fb30dc4181fe8797c6d497854 = false, $Ba405126142f0dfc9dbf39063b8e03f0 = '', $b1894fdfe80bfade7e52d132deb86670 = true, $e5da5890532f44eaec7109ff806fa870 = '') {
        if (!($this->dbh && $this->result)) {
            return false;
        }
        $Cd4eabf7ecf553f46c17f0bd5a382c46 = array();
        if (!($this->d1E5ce3b87bb868b9E6eFd39AA355A4F() > 0)) {
            goto Fdf8f732442f344d5925f71f1931e7b6;
        }
        e3988826b887ae0e6e46fbfa14b3f173:
        if (!($c72d66b481d02f854f0bef67db92a547 = mysqli_fetch_array($this->result, MYSQLI_ASSOC))) {
            Fdf8f732442f344d5925f71f1931e7b6:
            mysqli_free_result($this->result);
            return $Cd4eabf7ecf553f46c17f0bd5a382c46;
        }
        if ($E4cee08fb30dc4181fe8797c6d497854 && array_key_exists($Ba405126142f0dfc9dbf39063b8e03f0, $c72d66b481d02f854f0bef67db92a547)) {
            if (isset($Cd4eabf7ecf553f46c17f0bd5a382c46[$c72d66b481d02f854f0bef67db92a547[$Ba405126142f0dfc9dbf39063b8e03f0]])) {
                goto c304785c86f87c9503cf9e3aece77e5f;
            }
            $Cd4eabf7ecf553f46c17f0bd5a382c46[$c72d66b481d02f854f0bef67db92a547[$Ba405126142f0dfc9dbf39063b8e03f0]] = array();
            c304785c86f87c9503cf9e3aece77e5f:
            if (!$b1894fdfe80bfade7e52d132deb86670) {
                if (!empty($e5da5890532f44eaec7109ff806fa870) && array_key_exists($e5da5890532f44eaec7109ff806fa870, $c72d66b481d02f854f0bef67db92a547)) {
                    $Cd4eabf7ecf553f46c17f0bd5a382c46[$c72d66b481d02f854f0bef67db92a547[$Ba405126142f0dfc9dbf39063b8e03f0]][$c72d66b481d02f854f0bef67db92a547[$e5da5890532f44eaec7109ff806fa870]] = $c72d66b481d02f854f0bef67db92a547;
                    goto F517e50d68da24a50f3e983ffd421213;
                }
                $Cd4eabf7ecf553f46c17f0bd5a382c46[$c72d66b481d02f854f0bef67db92a547[$Ba405126142f0dfc9dbf39063b8e03f0]][] = $c72d66b481d02f854f0bef67db92a547;
                F517e50d68da24a50f3e983ffd421213:
                goto D782c7a4b5f6a9a39e5cab45dbd98ae0;
            }
            $Cd4eabf7ecf553f46c17f0bd5a382c46[$c72d66b481d02f854f0bef67db92a547[$Ba405126142f0dfc9dbf39063b8e03f0]] = $c72d66b481d02f854f0bef67db92a547;
            D782c7a4b5f6a9a39e5cab45dbd98ae0:
            goto Df70de21938ce8ff8fcc8e19e9f72926;
        }
        $Cd4eabf7ecf553f46c17f0bd5a382c46[] = $c72d66b481d02f854f0bef67db92a547;
        Df70de21938ce8ff8fcc8e19e9f72926:
        goto e3988826b887ae0e6e46fbfa14b3f173;
    }
    public function f1Ed191d78470660edFf4a007696bc1F() {
        if (!($this->dbh && $this->result)) {
            return false;
        }
        $c72d66b481d02f854f0bef67db92a547 = array();
        if (!($this->d1E5Ce3b87Bb868b9e6EFD39aa355A4F() > 0)) {
            goto Ce3310d3ae9e7245600834f20687e69a;
        }
        $c72d66b481d02f854f0bef67db92a547 = mysqli_fetch_array($this->result, MYSQLI_ASSOC);
        Ce3310d3ae9e7245600834f20687e69a:
        mysqli_free_result($this->result);
        return $c72d66b481d02f854f0bef67db92a547;
    }
    public function b98CE8B3899E362093173CC5EB4146b9() {
        if (!($this->dbh && $this->result)) {
            return false;
        }
        $c72d66b481d02f854f0bef67db92a547 = false;
        if (!($this->d1E5ce3b87Bb868B9E6EfD39Aa355A4f() > 0)) {
            goto ac328ab33eec70cd683cad1c13f5a57e;
        }
        $c72d66b481d02f854f0bef67db92a547 = mysqli_fetch_array($this->result, MYSQLI_NUM);
        $c72d66b481d02f854f0bef67db92a547 = $c72d66b481d02f854f0bef67db92a547[0];
        ac328ab33eec70cd683cad1c13f5a57e:
        mysqli_free_result($this->result);
        return $c72d66b481d02f854f0bef67db92a547;
    }
    public function E872be457a7f493D774179c6bdF95B46() {
        $Aca1086fdec6d6d95b8fcf9874828bbd = mysqli_affected_rows($this->dbh);
        return empty($Aca1086fdec6d6d95b8fcf9874828bbd) ? 0 : $Aca1086fdec6d6d95b8fcf9874828bbd;
    }
    public function FC53e22aE7Ee3Bb881cd95fb606914F0($b0f1eb357ed72245e03dfe6268912497) {
        $this->cc637BCb0B74b82beBC2776607E73bED();
        $this->result = mysqli_query($this->dbh, $b0f1eb357ed72245e03dfe6268912497);
        if ($this->result) {
            return true;
        }
        a78bf8D35765BE2408c50712CE7a43Ad::E501281ad19Af8A4Bbbf9bED91ee9299("MySQL Query Failed [" . $b0f1eb357ed72245e03dfe6268912497 . "]: " . mysqli_error($this->dbh));
        return false;
    }
    public function escape($F999d6c638356ee8a5d971e3eabf821a) {
        $this->cc637bCb0b74B82bEBc2776607e73beD();
        return mysqli_real_escape_string($this->dbh, $F999d6c638356ee8a5d971e3eabf821a);
    }
    public function cB6eeD7307EC2a13aE58F83987d0629f() {
        $Dd875708f3436837199ecc6210211f1d = mysqli_num_fields($this->result);
        return empty($Dd875708f3436837199ecc6210211f1d) ? 0 : $Dd875708f3436837199ecc6210211f1d;
    }
    public function bEb8A0bba80A0133a23fE13d34Dc94d6() {
        $c56e7d2361269a789b7e90324217084b = mysqli_insert_id($this->dbh);
        return empty($c56e7d2361269a789b7e90324217084b) ? 0 : $c56e7d2361269a789b7e90324217084b;
    }
    # Get num rows
    public function d1E5cE3B87bb868B9e6efd39Aa355A4F() {
        $A1b53d06894cd6b024ae321063e5a015 = mysqli_num_rows($this->result);
        return empty($A1b53d06894cd6b024ae321063e5a015) ? 0 : $A1b53d06894cd6b024ae321063e5a015;
    }
}

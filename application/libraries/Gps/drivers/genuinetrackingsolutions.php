<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Gps_genuinetrackingsolutions Class
 */
class genuinetrackingsolutions extends CI_Driver implements GpsInterface
{
    private $loginUrl = "http://login.genuinetrackingsolutions.com/GTS/login";

    /**
     * @see GpsInterface::login()
     */

    public function tracks()
    {
        return $this->_getData('http://login.genuinetrackingsolutions.com/GTS/trackerlist', ['op' => 'trackerTree']);
    }

    public function currentTracks()
    {
        return $this->_getData('http://login.genuinetrackingsolutions.com/GTS/trackerlist', ['op' => 'currentlist']);
    }

    public function parkings($id, $date)
    {
        $queryString =
            'op=detailreport&' .
            'reportType=parking&' .
            'unitList=' . $id . '&' .
            'sdate=' . date('d/m/Y%2000:00', strtotime($date)) . '&' .
            'edate=' . date('d/m/Y%2023:59', strtotime($date)) . '&' .
            'parkingMin=3';

        return $this->_getData('http://login.genuinetrackingsolutions.com/GTS/report?' . $queryString, [
            'start' => '0',
            'length' => '50000',
        ]);
    }

    public function distance($id, $date)
    {
        $query = [
            'op' => 'detailreport',
            'reportType' => 'distance',
            'unitList' => $id,
            'sdate' => date('d/m/Y 00:00', strtotime($date)),
            'edate' => date('d/m/Y 23:59', strtotime($date)),
            'startSpeed' => 5,
            'parkingMin' => 5
        ];
        return $this->_getData('http://login.genuinetrackingsolutions.com/GTS/report', $query);
    }

    public function route($id, $date)
    {
        $query = [
            'op' => 'getReportTrackList',
            'trackerId' => $id,
            'sdate' => date('d/m/Y 00:00', strtotime($date)),
            'edate' => date('d/m/Y 23:59', strtotime($date)),
            'length' => '50000',
            'q' => '1',
            'reportType' => 'historyreport',
            'speed' => 0,
            'soperator' => '>=',
        ];

        return $this->_getData('http://login.genuinetrackingsolutions.com/GTS/reporttrack', $query);
    }

    private function _getData($url, $post = [])
    {
        $try = 0;
        $errors = [];
        do {
            try {
                $res = $this->_sendPost($url, $post);
                if (false !== strpos($res['redirectURL'], $this->loginUrl)) {
                    $errors[] = 'not logged in';
                    $this->_login();
                    $try++;
                } elseif ($res['httpCode'] != 200) {
                    $errors[] = 'HTTP Code is: ' . $res['httpCode'];
                    $try++;
                } elseif (strlen($res['result']) == 0) {
                    $errors[] = 'empty result set!';
                } else {
                    return $res['result'];
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                $try++;
                continue;
            }
        } while ($try < 3);
        if (!empty($errors))
            show_error(implode('\r\n', $errors));
        else
            show_error('Number of request attempts exceeded!');
        return;
    }

    protected function _login()
    {
        $res = $this->_sendPost($this->loginUrl, [
            'userId' => $this->getCredentials('login'),
            'password' => $this->getCredentials('password'),
            'tz' => $this->_getTz()
        ]);
        if (!strpos($res['result'], 'data-original-title="Logout"')) {
            throw new Exception("Login failed!");
        }
        return true;
    }

    protected function _sendPost($url, $post, $cookie = NULL)
    {
        $query = http_build_query($post);
        $headers = $this->_getHeaders($query);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.79 Safari/537.36');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        if ($query)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        $result = curl_exec($ch);
        $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        return [
            'redirectURL' => $redirectURL,
            'httpCode' => $httpCode,
            'result' => $result
        ];
    }

    protected function _getHeaders($postQuery = NULL)
    {
        return [
            'Accept:*/*',
            //'Accept-Encoding:gzip, deflate',
            'Accept-Language:ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4,tr;q=0.2,uk;q=0.2',
            'Cache-Control:no-cache',
            'Connection:keep-alive',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Host: login.genuinetrackingsolutions.com',
            /*'Content-Length: ' . (strlen($postQuery) * 2),*/
            'Origin:http://login.genuinetrackingsolutions.com',
            'Pragma:no-cache',
            'Referer:http://login.genuinetrackingsolutions.com/GTS/map.jsp',
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.79 Safari/537.36',
            'X-Requested-With:XMLHttpRequest',
        ];
    }

    protected function _getTz()
    {
        $dateTimeZone = new DateTimeZone(date_default_timezone_get());
        $dateTime = new DateTime('now', $dateTimeZone);
        return $dateTimeZone->getOffset($dateTime) / 60;
    }
}
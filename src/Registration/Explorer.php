<?php
/**
 * Created by PhpStorm.
 * User: onur
 * Date: 06/06/2017
 * Time: 22:15
 */

namespace Registration;


use Sunra\PhpSimple\HtmlDomParser;

class Explorer
{
    private $username;
    private $password;

    /** @var Fetch $fetch */
    private $fetch;

    /**
     * Explorer constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->fetch = new Fetch("/tmp/$username.cookie", "/tmp/$username.cookie");
    }

    public function login()
    {
        $login_result = $this->fetch->post("https://registration.boun.edu.tr/scripts/stuinflogin.asp", [
            'user_name' => $this->username,
            'user_pass' => $this->password
        ]);

        if (stristr($login_result, "hatakullanici") !== false) {
            return false;
        } else {
            return true;
        }


    }

    public function fetchGrades($semester)
    {
        $this->login();

        $output = $this->fetch->get("http://registration.boun.edu.tr/scripts/stuinfgs.asp?donem=$semester");


        if ($this->checkIfLogin($output)) {

            try {
                $dom = HtmlDomParser::str_get_html($output);

                if (!$dom)
                    return false;

            } catch (\Exception $e) {
                return false;
            }


            $grades = ['spa' => 0, 'gpa' => 0, 'courses' => []];

            try {
                $table = $dom->find('table', 1);
                if (!$table)
                    return false;
                $elements = $table->find("tr[class=recmenu]");
            } catch (\Exception $e) {
                return false;
            }

            foreach ($elements as $element) {
                $course = str_replace("&nbsp;", "", $element->find("td", 0)->plaintext);
                $grade = str_replace("&nbsp;", "", $element->find("td", 3)->plaintext);

                if ($grade == "") {
                    $grade = "NA";
                }

                $grades['courses'][$course] = $grade;
            }

            $table = $dom->find("table", 2);

            if (!$table)
                return $grades;

            $spa_tr = $table->find("tr", 0);

            if (!$spa_tr)
                return $grades;

            $spa_txt = $spa_tr->find("td", 2)->plaintext;

            $gpa_tr = $table->find("tr", 1);

            if (!$gpa_tr)
                return $grades;

            $gpa_txt = $gpa_tr->find("td", 2)->plaintext;

            $spa = str_replace(["SPA:", ","], ["", "."], $spa_txt);
            $gpa = str_replace(["GPA*:", ","], ["", "."], $gpa_txt);

            $grades['gpa'] = $gpa;
            $grades['spa'] = $spa;

            return $grades;
        }

        return [];

    }

    public function fetchSchedule()
    {
        $this->login();

        $output = $this->fetch->get("http://registration.boun.edu.tr/scripts/stuinfsc.asp");


        if ($this->checkIfLogin($output)) {

            try {
                $dom = HtmlDomParser::str_get_html($output);

                if (!$dom)
                    return false;

            } catch (\Exception $e) {
                return false;
            }


            $courses = [];

            try {
                $table = $dom->find('table', 1);

                if (!$table)
                    return false;
            } catch (\Exception $e) {
                return false;
            }

            try {
                $elements = $table->find('tr[class=recmenu]');

                if (!$elements)
                    return false;
            } catch (\Exception $e) {
                return false;
            }


            foreach ($elements as $element) {

                $schedule = [];

                $name = $element->find("td", 0)->plaintext;
                $days_hours = $element->find("td", 1)->plaintext;
                $locations_text = $element->find("td", 2)->plaintext;

                list($days_text, $hours_text) = explode("&nbsp;", $days_hours);

                $days_text = str_replace('Th', 'X', $days_text);
                $days_text = str_replace(['M', 'T', 'W', 'X', 'F'], [0, 1, 2, 3, 4], $days_text);

                $days = str_split($days_text);
                $hours = str_split($hours_text);


                $locations_text = str_replace('&nbsp;', '', $locations_text);

                $locations = array_map(function ($elem) {
                    return trim($elem);
                }, explode("|", $locations_text));


                for ($i = 0; $i < count($days); $i++) {

                    $key = $days[$i] . "-" . $locations[$i];

                    if (!array_key_exists($key, $schedule)) {
                        $schedule[$key] = [
                            'day' => (int)$days[$i],
                            'location' => $locations[$i],
                            'hours' => []
                        ];
                    }

                    $schedule[$key]['hours'][] = (int)$hours[$i];
                }

                $day_names = [
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday'
                ];

                foreach ($schedule as $key => $value) {

                    $schedule[$key]['day_text'] = $day_names[$value['day']];
                    $schedule[$key]['start_text'] = sprintf('%01d', min($value['hours']) + 8) . ":00";
                    $schedule[$key]['end_text'] = sprintf('%01d', max($value['hours']) + 8) . ":50";
                }


                $courses[] = [
                    'name' => $name,
                    'schedule' => $schedule
                ];
            }

            return $courses;
        }
    }

    public function fetchBuCardDetails()
    {
        $this->login();

        $output = $this->fetch->get("http://registration.boun.edu.tr/scripts/buis_gate.asp?p=BUCardDiningLogs", true);


        if ($this->checkIfLogin($output)) {

            try {
                $dom = HtmlDomParser::str_get_html($output);

                if (!$dom)
                    return false;

            } catch (\Exception $e) {
                return false;
            }


            $cards = [];

            try {
                $table = $dom->find('table[class=tblCards]', 0);

                if (!$table)
                    return false;

                $elements = $table->find("tr");
            } catch (\Exception $e) {
                return false;
            }

            $header = true;

            foreach ($elements as $element) {

                if ($header) {
                    $header = false;
                    continue;
                }

                $number = $element->find("th", 1)->plaintext;
                $balance = $element->find("th", 5)->plaintext;

                $cards[$number] = $balance;
            }

            return $cards;
        }

        return [];

    }

    private function checkIfLogin($output)
    {
        if (stristr($output, "studententry") !== false) {
            return $this->login();
        }

        if (stristr($output, "a new password policy") !== false) {
            return false;
        }

        return true;
    }
}
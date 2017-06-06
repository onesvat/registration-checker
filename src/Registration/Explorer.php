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
        echo 'hi';
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

            $dom = HtmlDomParser::str_get_html($output);

            $grades = ['spa' => 0, 'gpa' => 0, 'courses' => []];

            foreach ($dom->find('table', 1)->find("tr[class=recmenu]") as $element) {
                $course = str_replace("&nbsp;", "", $element->find("td", 0)->plaintext);
                $grade = str_replace("&nbsp;", "", $element->find("td", 3)->plaintext);

                if ($grade == "") {
                    $grade = "NA";
                }

                $grades['courses'][$course] = $grade;
            }

            return $grades;
        }

        return [];

    }

    private function checkIfLogin($output)
    {
        if (stristr($output, "studententry") !== false) {
            return $this->login();
        }

        return true;
    }
}
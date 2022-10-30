<?php namespace MotionArray\Billing;

use Config;
use GuzzleHttp\Client;

/**
 * Payoneer Billing class
 *
 * This a cut-down version of the actual Payoneer API which is too terrible
 * to include in it's entirety.
 */
class Payoneer
{
    /**
     * Return api parameters (p1, p2, p3)
     * @return array
     */
    public function getParameterArray()
    {
        $p1 = Config::get('payoneer.username'); // partner username
        $p2 = Config::get('payoneer.password'); // partner password
        $p3 = Config::get('payoneer.partner_id'); // partner ID

        return ['p1' => $p1, 'p2' => $p2, 'p3' => $p3];
    }

    /**
     * Creates a function called 'openurl' which passes through two arguments, mname and parameters
     */
    public function openurl($mname, $parameters)
    {
        $p1 = Config::get('payoneer.username'); // partner username
        $p2 = Config::get('payoneer.password'); // partner password
        $p3 = Config::get('payoneer.partner_id'); // partner ID

        // this gets the contents from the URL and sets it to the $signup_link variable
        $signup_link = file_get_contents(Config::get('payoneer.api_url') . "/Payouts/HttpApi/API.aspx?mname=$mname&p1=$p1&p2=$p2&p3=$p3" . $parameters);

        // returns the signup_link in order to all it to be used as the final product in the user files
        return $signup_link;
    }


    /**
     * Creates a function called 'parser' which passes through one argument, signup_link
     */
    public function parser($signup_link)
    { //
        // this method parses the string and sets it to the $xml variable
        $xml = simplexml_load_string($signup_link);

        // returns the xml in order for it to be used in the user files
        return $xml;
    }


    /**
     * Creates a function called 'response' which passes through the arguments mname, xml,  and signup_link
     */
    public function response($mname, $xml, $signup_link)
    {
        // if mname equals GetToken, continue here
        if ($mname == 'GetToken') {
            // if the xml string contains the value of Token, continue here
            if (stristr($signup_link, 'Token') == TRUE) {
                // sets the header location to the signup_link variable
                header("Location: $signup_link");
            } else // otherwise continue here
            {
                echo 'Error Code: '; // prints out error code:
                echo($xml->Code); // prints out the value betweent the code tags
                echo '<br>'; // creates a line break
                echo 'Description: '; // prints out the description:
                echo($xml->Description); // prints out the value between the description tags
            }
        } else {
            echo 'Error Code: '; // print "Error Code"
            echo($xml->Code); // prints the error code
            echo '<br>';
            echo 'Description: '; // print "Description"
            echo($xml->Description); // prints the Description
        }
    }

    public function getPayee($payeeId)
    {
        $client = new Client();

        $url = config('payoneer.api_url') . '/Payouts/HttpApi/API.aspx'
            . '?mname=GetPayeeDetails';

        $params = $this->getParameterArray();
        $params['p4'] = $payeeId;

        $url .= '&'.http_build_query($params);

        $response = $client->post($url);

        $xml = $response->getBody()->getContents();

        return simplexml_load_string($xml);
    }

}

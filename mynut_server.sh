#!/bin/sh

# echo $(date) $(date +%s) $(/home/seal/nuttcp -F -fparse 10.140.0.1) >> bwdl.txt
# echo $(date) $(date +%s) $(/home/seal/nuttcp -fparse myspeed.ogonan.com) >> bwul.txt

/home/seal/nuttcp  -t 10.140.0.6 > foutput
if cat foutput | grep -q 'Error:'; then
        echo "Error"
		echo $(date) $(date +%s) $(cat foutput) >> bwdl.txt
        cat foutput |  awk '{print "[{\"mbps\":\"" 0 "\", \"rtt_ms\":\"" 0 "\", "}' > array_data.json
        echo \"date\"\:\"$(date +"%Y-%m-%dT%H:%M:%S%z")\"\, \"epoch\"\:\"$(date +%s)\", \"local_host\"\:\"\
        $(ip addr | grep 'eth0:' -A2 | tail -n1 | awk '{print $2}' | cut -f1  -d'/')\"\, \"dl\"\:\"Download\"\}\, >> array_data.json
else
        echo $(date) $(date +%s) $(cat foutput) >> bwdl.txt
        cat foutput |  awk '{print "[{\"mbps\":\"" $7 "\", \"rtt_ms\":\"" $15 "\", "}' > array_data.json
        echo \"date\"\:\"$(date +"%Y-%m-%dT%H:%M:%S%z")\"\, \"epoch\"\:\"$(date +%s)\", \"local_host\"\:\"\
        $(ip addr | grep 'eth0:' -A2 | tail -n1 | awk '{print $2}' | cut -f1  -d'/')\"\, \"dl\"\:\"Download\"\}\, >> array_data.json
fi

sleep 20

/home/seal/nuttcp -r  10.140.0.6  > output
if cat output | grep -q 'Error:'; then
        echo "Error"
		echo $(date) $(date +%s) $(cat output) >> bwul.txt
        cat output |  awk '{print "{\"mbps\":\"" 0 "\", \"rtt_ms\":\"" 0 "\", "}' >> array_data.json
        echo \"date\"\:\"$(date +"%Y-%m-%dT%H:%M:%S%z")\"\, \"epoch\"\:\"$(date +%s)\", \"local_host\"\:\"\
        $(ip addr | grep 'eth0:' -A2 | tail -n1 | awk '{print $2}' | cut -f1  -d'/')\"\, \"dl\"\:\"Upload\"\}\] >> array_data.json
else
        echo $(date) $(date +%s) $(cat output) >> bwul.txt
        cat output |  awk '{print "{\"mbps\":\"" $7 "\", \"rtt_ms\":\"" $15 "\", "}' >> array_data.json
        echo \"date\"\:\"$(date +"%Y-%m-%dT%H:%M:%S%z")\"\, \"epoch\"\:\"$(date +%s)\", \"local_host\"\:\"\
        $(ip addr | grep 'eth0:' -A2 | tail -n1 | awk '{print $2}' | cut -f1  -d'/')\"\, \"dl\"\:\"Upload\"\}\] >> array_data.json
fi

curl -d @array_data.json --header "Content-Type: application/json" http://myspeed.ogonan.com/mynut_array.php
cat array_data.json  >> data.json

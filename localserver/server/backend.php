<?php
// Enable error reporting
ini_set("log_errors", 1);
ini_set("error_log", "/home/pi/localserver/server/error.log");

function netmaskToCIDR($netmask) {
    $long = ip2long($netmask);
    return $long ? substr_count(decbin($long), '1') : 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $communicationType = $_POST['communicationType'];
    $ipAddress = $_POST['ipAddress'] ?? '';
    $netmask = $_POST['netmask'] ?? '';
    $gateway = $_POST['gateway'] ?? '';
    $dns1 = $_POST['dns1'] ?? '';
    $simApn = $_POST['simApn'] ?? '';
    $ipv4Config = $_POST['ipv4Config'] ?? ''; // Get selection (Automatic/Manual)
 

    try {
        $output = [];
        $resultCode = 0;

        // Configure the chosen network type
        if ($communicationType === "WAN") {
       
            exec("sudo nmcli con show | grep 'wan' | awk '{print $1}'", $output, $resultCode);
            if (!empty($output)) {
                exec("sudo nmcli con delete wan", $output, $resultCode);
                error_log("Deleted existing WAN connection before reconfiguring.");
            }
        
            if ($ipv4Config === "automatic") {
                // ✅ STEP 2: CONFIGURE WAN WITH DHCP (AUTOMATIC MODE)
                exec("sudo nmcli con add type ethernet con-name wan ifname eth0 ipv4.method auto 2>&1", $output, $resultCode);
                error_log("WAN configured with DHCP: " . implode("\n", $output));
        
                if ($resultCode !== 0) {
                    throw new Exception("Failed to configure WAN with DHCP. Check error log for details.");
                }
            } else {
            
                $ipAddress = $_POST['ipAddress'] ?? '';
                $netmask = $_POST['netmask'] ?? '';
                $gateway = $_POST['gateway'] ?? '';
                $dns1 = $_POST['dns1'] ?? '';
                $dns2 = $_POST['dns2'] ?? '';
        
                if (!empty($ipAddress) && !empty($netmask) && !empty($gateway) && !empty($dns1)) {
                    $cidr = netmaskToCIDR($netmask);
                    if ($cidr === 0) {
                        throw new Exception("Invalid netmask provided.");
                    }
                    exec("sudo nmcli con show | grep 'wan' | awk '{print $1}'", $output, $resultCode);
                    if (!empty($output)) {
                        exec("sudo nmcli con delete wan", $output, $resultCode);
                        error_log("Deleted existing WAN connection before reconfiguring.");
                    }
                
                    exec("sudo nmcli con add type ethernet con-name wan ifname eth0 ip4 $ipAddress/$cidr gw4 $gateway 2>&1", $output, $resultCode);
                    error_log("WAN configuration command output: " . implode("\n", $output));
        
                    if ($resultCode !== 0) {
                        throw new Exception("Failed to configure WAN. Check error log for details.");
                    }
        
                    exec("sudo nmcli con mod wan ipv4.dns '$dns1 $dns2' 2>&1", $output, $resultCode);
                    error_log("WAN DNS configuration command output: " . implode("\n", $output));
        
                    if ($resultCode !== 0) {
                        throw new Exception("Failed to configure WAN DNS. Check error log for details.");
                    }
                } else {
                    throw new Exception("Missing required parameters for manual WAN configuration.");
                }
            }
        
            echo "WAN configuration successful!";
        }elseif ($communicationType === "WIFI") {
            $ssid = $_POST['ssid'];
            $password = $_POST['password'];
            $countryCode = $_POST['countryCode']; // Get country code from form
        
            error_log("SSID: " . $ssid);
            error_log("Password: " . $password);
            error_log("Country Code: " . $countryCode);
        
            // Bring down MyHotspot connection
            exec("sudo nmcli connection down MyHotspot", $output, $resultCode);
            sleep(2);
            if ($resultCode !== 0) {
                throw new Exception("Failed to bring down MyHotspot.");
            }
        
            // Set country code
            exec("sudo iw reg set $countryCode", $output, $resultCode);
            sleep(1);
            if ($resultCode !== 0) {
                throw new Exception("Failed to set country code.");
            }
        
            // Update existing connection profile with new SSID and password
            exec("sudo nmcli connection modify Wifi 802-11-wireless.ssid '$ssid'", $output, $resultCode);
            if ($resultCode !== 0) {
                throw new Exception("Failed to update SSID.");
            }
        
            exec("sudo nmcli connection modify Wifi 802-11-wireless-security.psk '$password'", $output, $resultCode);
            if ($resultCode !== 0) {
                throw new Exception("Failed to update password.");
            }
        
            // Bring up the connection again
            exec("sudo nmcli connection up Wifi", $output, $resultCode);
            sleep(5);
            if ($resultCode !== 0) {
                throw new Exception("Failed to reconnect to Wi-Fi.");
            }
            exec("nmcli connection modify Wifi autoconnect yes", $output, $resultCode);
            if ($resultCode !== 0) {
                throw new Exception("Failed to set autoconnect for Wi-Fi.");
            }
        
            error_log("Wi-Fi configuration updated successfully.");
        }
        
      
         elseif ($communicationType === "SIM" && !empty($simApn)) {
            exec("sudo nmcli connection modify harp-gsm gsm.apn $simApn 2>&1", $output, $resultCode);
            error_log("SIM configuration command output: " . implode("\n", $output));
            if ($resultCode !== 0) {
                throw new Exception("Failed to configure SIM APN. Check error log for details.");
            }
        }
      
        
    
        echo "Configuration applied successfully! Please Power off the device and insert sim/wan and power on ...";
      

        // show like if user click sim .. then page shows . poweroff and insert sim and on // if user click wifi . power off and on . if user clicks wan . connect cable and poweron
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
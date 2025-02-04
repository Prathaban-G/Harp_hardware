#!/bin/bash

# Turn off the preconfigured connection and prevent auto-reconnect
nmcli connection down Wifi
#nmcli connection modify "preconfigured" autoconnect no

# Enable Wi-Fi and set wlan0 to managed mode
sudo rfkill unblock wifi
sudo nmcli device set wlan0 managed yes
sudo nmcli device reapply wlan0

# Check device status
nmcli device status

# Create a new hotspot connection
#nmcli connection add type wifi ifname wlan0 mode ap con-name MyHotspot ssid Harp_Hardware
#nmcli connection modify MyHotspot 802-11-wireless.band bg
#nmcli connection modify MyHotspot 802-11-wireless.channel 6
#nmcli connection modify MyHotspot wifi-sec.key-mgmt wpa-psk
#nmcli connection modify MyHotspot wifi-sec.psk "harp_2025"
#nmcli connection modify MyHotspot ipv4.addresses 192.168.4.1/24
#nmcli connection modify MyHotspot ipv4.method shared

# Bring up the hotspot
nmcli connection up MyHotspot

# Start the local server
cd /home/pi/Harp_hardware/localserver/server
#python3 -m http.server 8080 &
#php -S localhost:8000
php -S 0.0.0.0:8000

# Keep the script running
while true; do
    sleep 3600  # Sleep for 1 hour (or any other duration)
done
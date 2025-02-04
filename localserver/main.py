import subprocess
import time

def check_ip_addresses():
    interfaces = subprocess.getoutput("ls /sys/class/net").split()
    
    # Exclude 'lo' and 'eth1'
    excluded_interfaces = {'lo', 'eth1'}
    interfaces = [iface for iface in interfaces if iface not in excluded_interfaces]

    for interface in interfaces:
        ip = subprocess.getoutput(f"ip -4 addr show {interface} | grep inet | awk '{{print $2}}'").strip()
        if ip:
            print(f"IP address found on {interface}: {ip}")
            return True

    print("No IP address found on any interface (excluding eth1 and lo).")
    return False

def start_local_server():
    print("Starting local server...")
    subprocess.run(["sudo", "systemctl", "start", "localserver.service"])
    time.sleep(600)  # 10 minutes
    print("Rebooting system...")
    subprocess.run(["sudo", "reboot"])

def main():
    print("Waiting for 2 minutes...")
    time.sleep(120)  # Corrected to 2 minutes (120 seconds)
    
    if not check_ip_addresses():
        start_local_server()

if __name__ == "__main__":
    main()


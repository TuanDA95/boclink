<?php
ob_start();
header('Content-Type: application/x-apple-aspen-config');
header('Content-Disposition: inline; filename="gmvmoba.mobileconfig"');

$callbackUrl = "https://key.gmvmoba.com/udid/done";
$org = "GMVMOBA System";
$id = "com.gmvmoba.udid." . time(); 

echo '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>PayloadContent</key>
    <dict>
        <key>URL</key>
        <string>' . $callbackUrl . '</string>
        <key>DeviceAttributes</key>
        <array>
            <string>UDID</string>
            <string>SERIAL</string>
            <string>VERSION</string>
            <string>PRODUCT</string>
        </array>
    </dict>
    <key>PayloadOrganization</key>
    <string>' . $org . '</string>
    <key>PayloadDisplayName</key>
    <string>Xac Minh Thiet Bi GMVMOBA</string>
    <key>PayloadVersion</key>
    <integer>1</integer>
    <key>PayloadUUID</key>
    <string>' . md5(time()) . '</string>
    <key>PayloadIdentifier</key>
    <string>' . $id . '</string>
    <key>PayloadType</key>
    <string>ProfileService</string>
</dict>
</plist>';
ob_end_flush();
exit;
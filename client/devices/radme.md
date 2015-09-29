# Debugging

## Android

Run:
    /Users/sky//Library/Android/sdk/platform-tools/adb -s emulator-5554 logcat

# Deploying

## Android

Check out this tutorial
http://ionicframework.com/docs/guide/publishing.html

jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore keys/netric-release-key.keystore platforms/android/build/outputs/apk/android-release-unsigned.apk netric

zipalign -v 4 platforms/android/build/outputs/apk/android-release-unsigned.apk platforms/android/build/outputs/apk/android-release.apk
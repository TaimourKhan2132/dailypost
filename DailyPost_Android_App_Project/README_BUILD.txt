DAILYPOST ANDROID APP
=====================

A WebView wrapper around https://dailypostpk.page.gd/
The website remains the single source of content.

BUILD
-----
1. Install Android Studio.
2. Open this folder as a project and let Gradle sync.
   (Android Studio will offer to create the Gradle wrapper if it is
   missing - accept.)
3. Build > Build Bundle(s)/APK(s) > Build APK(s).
4. The debug APK appears in app/build/outputs/apk/debug/.


BEFORE RELEASING
----------------
* The site address is the constant HOST in MainActivity.java. It is
  used both to load the page and to decide which links are external.
  When the site moves to dailypost.com.pk, change it there - one
  place - and rebuild.

* Create a signed release build, not the debug APK. Google Play also
  requires an .aab rather than an .apk.

* Play rejects apps that are only a website with nothing added. This
  project has offline handling, pull-to-refresh, back navigation and
  proper external-link handling, which is the usual minimum.


WHAT WAS FIXED IN THIS VERSION
------------------------------
The first draft had several problems:

1. Share buttons did nothing. shouldOverrideUrlLoading returned true
   for external links - telling Android "handled" - but never opened
   anything. WhatsApp, Facebook and X links silently died. They now
   fire an ACTION_VIEW intent.

2. The launcher icon was a vector used as android:icon. Android does
   not support that below API 26, and minSdk is 23, so the icon would
   have been blank on Android 6 and 7. Replaced with PNG mipmaps at
   five densities plus an adaptive icon for Android 8+.

3. splash_background.xml existed but no theme referenced it, so there
   was no splash screen despite it being listed as a feature. It is
   now the window background.

4. MainActivity extended android.app.Activity while the theme was an
   AppCompat one. Now extends AppCompatActivity.

5. gradle.properties was missing, so the build would stop with
   "This project uses AndroidX dependencies, but the
   'android.useAndroidX' property is not enabled."

6. Being offline showed a toast and then a blank white screen. There
   is now a bundled offline page.

7. ACCESS_NETWORK_STATE was missing even though the code calls
   ConnectivityManager.

8. core-ktx was a dependency in a project with no Kotlin.

NOTE: none of this has been compiled - there is no Android Studio on
the machine it was written on. The problems above were found by
reading the code. Expect to fix small things on first build.

package pk.dailypost.app;

import android.annotation.SuppressLint;
import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkCapabilities;
import android.net.Uri;
import android.os.Bundle;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

public class MainActivity extends AppCompatActivity {

    // Both the page to open and the test for "is this still our
    // site?". Kept in one place: when the site moves to
    // dailypost.com.pk only this constant changes.
    private static final String HOST = "dailypostpk.page.gd";
    private static final String HOME = "https://" + HOST + "/";

    private WebView webView;
    private SwipeRefreshLayout swipe;
    private boolean loadFailed = false;

    @SuppressLint("SetJavaScriptEnabled")
    @Override protected void onCreate(Bundle state) {
        super.onCreate(state);
        setContentView(R.layout.activity_main);

        swipe   = findViewById(R.id.swipe);
        webView = findViewById(R.id.webview);

        WebSettings s = webView.getSettings();
        s.setJavaScriptEnabled(true);
        // The dark-mode toggle keeps its setting in localStorage, so
        // without this the theme resets on every page.
        s.setDomStorageEnabled(true);
        s.setBuiltInZoomControls(false);
        s.setDisplayZoomControls(false);
        s.setUseWideViewPort(true);
        s.setLoadWithOverviewMode(false);
        s.setSupportMultipleWindows(false);

        webView.setWebViewClient(new WebViewClient() {

            @Override public boolean shouldOverrideUrlLoading(WebView v, WebResourceRequest r) {
                Uri uri = r.getUrl();
                String host = uri.getHost();

                // Anything on our own site stays in the app.
                if (host != null && host.equalsIgnoreCase(HOST)) {
                    return false;
                }

                // Everything else - the WhatsApp, Facebook and X share
                // links - has to be handed to the phone. The previous
                // version returned true here without opening anything,
                // so tapping share silently did nothing at all.
                try {
                    startActivity(new Intent(Intent.ACTION_VIEW, uri));
                } catch (ActivityNotFoundException e) {
                    Toast.makeText(MainActivity.this,
                            "No app available to open that link.", Toast.LENGTH_SHORT).show();
                }
                return true;
            }

            @Override public void onReceivedError(WebView v, WebResourceRequest req, WebResourceError err) {
                // Only care about the main page failing, not a single
                // missing image.
                if (req.isForMainFrame()) {
                    loadFailed = true;
                    v.loadUrl("file:///android_asset/offline.html");
                }
            }

            @Override public void onPageFinished(WebView v, String url) {
                swipe.setRefreshing(false);
            }
        });

        swipe.setOnRefreshListener(() -> {
            loadFailed = false;
            if (isOnline()) {
                webView.loadUrl(HOME);
            } else {
                swipe.setRefreshing(false);
                webView.loadUrl("file:///android_asset/offline.html");
            }
        });

        if (state != null) {
            webView.restoreState(state);
        } else if (isOnline()) {
            webView.loadUrl(HOME);
        } else {
            webView.loadUrl("file:///android_asset/offline.html");
        }
    }

    private boolean isOnline() {
        ConnectivityManager cm = (ConnectivityManager) getSystemService(CONNECTIVITY_SERVICE);
        if (cm == null) return false;
        NetworkCapabilities nc = cm.getNetworkCapabilities(cm.getActiveNetwork());
        return nc != null && nc.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET);
    }

    @Override public void onBackPressed() {
        if (webView.canGoBack() && !loadFailed) {
            webView.goBack();
        } else {
            super.onBackPressed();
        }
    }

    @Override protected void onSaveInstanceState(Bundle out) {
        super.onSaveInstanceState(out);
        webView.saveState(out);
    }
}

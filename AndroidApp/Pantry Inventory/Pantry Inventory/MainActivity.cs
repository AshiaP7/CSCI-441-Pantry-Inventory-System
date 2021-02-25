using System;
using Android.App;
using Android.OS;
using Android.Runtime;
using Android.Support.Design.Widget;
using Android.Support.V7.App;
using Android.Views;
using Android.Widget;
using Android.Webkit;
using ZXing;
using ZXing.Mobile;

namespace Pantry_Inventory
{
    [Activity(Label = "Pantry Inventory", Theme = "@style/AppTheme.NoActionBar", MainLauncher = true)]
    public class MainActivity : AppCompatActivity
    {
        MobileBarcodeScanner scanPage;
        WebView webView;
        protected override void OnCreate(Bundle savedInstanceState)
        {
            base.OnCreate(savedInstanceState);
            Xamarin.Essentials.Platform.Init(this, savedInstanceState);
            SetContentView(Resource.Layout.activity_main);
            webView = FindViewById<WebView>(Resource.Id.webView);
            webView.Settings.JavaScriptEnabled = true;
            webView.LoadUrl("http://hbprophecy.com/school/");

            Xamarin.Essentials.Platform.Init(Application);
            ZXing.Net.Mobile.Forms.Android.Platform.Init();

            Android.Support.V7.Widget.Toolbar toolbar = FindViewById<Android.Support.V7.Widget.Toolbar>(Resource.Id.toolbar);
            SetSupportActionBar(toolbar);
        }

        public override bool OnCreateOptionsMenu(IMenu menu)
        {
            MenuInflater.Inflate(Resource.Menu.menu_main, menu);
            return true;
        }

        public override bool OnOptionsItemSelected(IMenuItem item)
        {
            int id = item.ItemId;
            if (id == Resource.Id.action_settings)
            {
                return true;
            }
            if (id == Resource.Id.action_BarCode)
            {
                scanPage = new MobileBarcodeScanner();
                var result = scanPage.Scan();
                    //do something
                    Android.App.AlertDialog.Builder alertDialog = new Android.App.AlertDialog.Builder(this);
                    alertDialog.SetTitle("Simple Alert Dialog");
                    alertDialog.SetMessage("BarCode: " + result.Result.Text);
                    alertDialog.SetNeutralButton("OK", delegate
                    {
                        alertDialog.Dispose();
                    });
                    alertDialog.Show();
                
                return true;
            }

            return base.OnOptionsItemSelected(item);
        }

        private void FabOnClick(object sender, EventArgs eventArgs)
        {
            View view = (View) sender;
            Snackbar.Make(view, "Replace with your own action", Snackbar.LengthLong)
                .SetAction("Action", (Android.Views.View.IOnClickListener)null).Show();
        }

        public override void OnRequestPermissionsResult(int requestCode, string[] permissions, [GeneratedEnum] Android.Content.PM.Permission[] grantResults)
        {
            Xamarin.Essentials.Platform.OnRequestPermissionsResult(requestCode, permissions, grantResults);

            base.OnRequestPermissionsResult(requestCode, permissions, grantResults);
        }
	}
}

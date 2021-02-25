using System;
using Android.App;
using Android.OS;
using Android.Runtime;
using Android.Support.Design.Widget;
using Android.Support.V7.App;
using Android.Views;
using Android.Widget;
using Android.Webkit;
using System.Threading.Tasks;
using System.Text;
using System.Collections.Generic;
using ZXing;
using ZXing.Mobile;


namespace Pantry_Inventory
{
    [Activity(Label = "Pantry Inventory", Theme = "@style/AppTheme.NoActionBar", MainLauncher = true)]
    public class MainActivity : global::Xamarin.Forms.Platform.Android.FormsAppCompatActivity
    {
        MobileBarcodeScanner scanner;
        WebView webView;
 
        protected override void OnCreate(Bundle savedInstanceState)
        {
            base.OnCreate(savedInstanceState);
            Xamarin.Essentials.Platform.Init(this, savedInstanceState);
            global::Xamarin.Forms.Forms.Init(this, savedInstanceState);

            Xamarin.Essentials.Platform.Init(this, savedInstanceState);
            SetContentView(Resource.Layout.activity_main);
            
            webView = FindViewById<WebView>(Resource.Id.webView);
            webView.Settings.JavaScriptEnabled = true;
            webView.LoadUrl("http://hbprophecy.com/school/");

            Xamarin.Essentials.Platform.Init(Application);
            ZXing.Net.Mobile.Forms.Android.Platform.Init();

            Android.Support.V7.Widget.Toolbar toolbar = FindViewById<Android.Support.V7.Widget.Toolbar>(Resource.Id.toolbar);
            SetSupportActionBar(toolbar);

            //Create a new instance of our Scanner
            scanner = new MobileBarcodeScanner();
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
                OpenScanner();
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


        void HandleScanResult(ZXing.Result result)
        {
            var msg = "";

            if (result != null && !string.IsNullOrEmpty(result.Text))
                msg = "Found Barcode: " + result.Text;
            else
                msg = "Scanning Canceled!";

            RunOnUiThread(() => Toast.MakeText(this, msg, ToastLength.Short).Show());
        }


        public override void OnRequestPermissionsResult(int requestCode, string[] permissions, [GeneratedEnum] Android.Content.PM.Permission[] grantResults)
        {
            Xamarin.Essentials.Platform.OnRequestPermissionsResult(requestCode, permissions, grantResults);

            base.OnRequestPermissionsResult(requestCode, permissions, grantResults);

        }

        public async void OpenScanner()
        {

            scanner.UseCustomOverlay = false;

            //Start scanning
            var result = await scanner.Scan();

            HandleScanResult(result);
            
        }


        [Java.Interop.Export("UITestBackdoorScan")]
        public Java.Lang.String UITestBackdoorScan(string param)
        {
            var expectedFormat = BarcodeFormat.QR_CODE;
            Enum.TryParse(param, out expectedFormat);
            var opts = new MobileBarcodeScanningOptions
            {
                PossibleFormats = new List<BarcodeFormat> { expectedFormat }
            };
            var barcodeScanner = new MobileBarcodeScanner();

            Console.WriteLine("Scanning " + expectedFormat);

            //Start scanning
            barcodeScanner.Scan(opts).ContinueWith(t =>
            {

                var result = t.Result;

                var format = result?.BarcodeFormat.ToString() ?? string.Empty;
                var value = result?.Text ?? string.Empty;

                RunOnUiThread(() =>
                {

                    Android.App.AlertDialog dialog = null;
                    dialog = new Android.App.AlertDialog.Builder(this)
                                    .SetTitle("Barcode Result")
                                    .SetMessage(format + "|" + value)
                                    .SetNeutralButton("OK", (sender, e) =>
                                    {
                                        dialog.Cancel();
                                    }).Create();
                    dialog.Show();
                });
            });

            return new Java.Lang.String();
        }

    }
}

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
using System.Net.Http;
using Newtonsoft.Json; //change
using Android.Content;

namespace Pantry_Inventory
{
    [Activity(Label = "Pantry Inventory", Theme = "@style/AppTheme.NoActionBar", MainLauncher = true)]
    public class MainActivity : AppCompatActivity
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
            webView.SetWebViewClient(new MyWebViewClass());
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
                GetRequestUPC("052000208719");
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
            View view = (View)sender;
            Snackbar.Make(view, "Replace with your own action", Snackbar.LengthLong)
                .SetAction("Action", (Android.Views.View.IOnClickListener)null).Show();
        }


        void HandleScanResult(ZXing.Result result)
        {
            var msg = "";

            if (result != null && !string.IsNullOrEmpty(result.Text))
            {
                msg = "Found Barcode: " + result.Text;
                GetRequestUPC(result.Text);
                //webView.LoadUrl("http://hbprophecy.com/school/php/request?upc=" + result.Text);
            }
            else
                msg = "Scanning Canceled!";

            RunOnUiThread(() => Toast.MakeText(this, msg, ToastLength.Short).Show());
        }


        private async void GetRequestUPC(string upc)
        {
            try
            {
                HttpClient clientCabinets = new HttpClient();
                ItemList listeCabinets = null;
                string url = "https://api.upcitemdb.com/prod/trial/lookup?upc=" + upc;
                var uri = new Uri(string.Format(url, string.Empty));
                var response = await clientCabinets.GetAsync(uri);
                response.EnsureSuccessStatusCode();
                var jsonString = await response.Content.ReadAsStringAsync();
                if (response.IsSuccessStatusCode)
                {
                    listeCabinets = JsonConvert.DeserializeObject<ItemList>(jsonString);
                    Console.WriteLine("UPC GET: " + listeCabinets.Item[0].title);
                   if(listeCabinets.Item[0].images.Length > 0) Console.WriteLine("UPC GET: " + listeCabinets.Item[0].images[0]);
                    //create event and or navigation html to setup a post to our server and to display the image.
                    var intentConfirm = new Intent(this, typeof(ConfirmItem));
                    intentConfirm.PutExtra("image", listeCabinets.Item[0].images[0]);
                    this.StartActivity(intentConfirm);
                }
                else
                {
                    Console.WriteLine("UPC Failed: ");
                }
            }
            catch(Exception exception)
            {
                Console.WriteLine("CAUGHT EXCEPTION:");
                Console.WriteLine(exception);
            }

                
                // Console.WriteLine("Name" + listeCabinets[0].Item[0].title);
                //var builder = new Android.App.AlertDialog.Builder(this);
                //builder.SetTitle("UPC");
                //builder.SetNegativeButton("Okay", (EventHandler<DialogClickEventArgs>)null);
                //builder.SetMessage("UPC: " + upc);

            //var dialog = builder.Create();

            //var noBtn = dialog.GetButton((int)DialogButtonType.Negative);

            // Show the dialog. This is important to do before accessing the buttons.
            //dialog.Show();

            //noBtn.Click += (sender, args) =>
            // {
            // Dismiss dialog.
            ///   Console.WriteLine("I will dismiss now!");
            // dialog.Dismiss();
            //};
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

        public override void OnBackPressed()
        {
            //if (condition)
            //  super.onBackPressed(); // this close app
            //else
            // your code to open MainActivity
            //}
            if (webView.CanGoBack())
            {
                webView.GoBack();
                return;
            }
            else
            {
                base.OnBackPressed();
            }

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

using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Text;

using Android.App;
using Android.Content;
using Android.Graphics;
using Android.OS;
using Android.Runtime;
using Android.Views;
using Android.Widget;

namespace Pantry_Inventory
{
    [Activity(Label = "ConfirmItem", Exported = true)]
    public class ConfirmItem : Activity
    {
        private string str_upc;
        private string str_name;
        private string str_brand;
        private int iquantity;
        protected override void OnCreate(Bundle savedInstanceState)
        {
            base.OnCreate(savedInstanceState);

            // Create your application here
            SetContentView(Resource.Layout.ConfirmItem);
            LoadImageLink(Intent.GetStringExtra("image"));
            Button confirmbutton = FindViewById<Button>(Resource.Id.buttonconfirm);
            confirmbutton.Click += delegate {

                EditText textquantity = (EditText)FindViewById(Resource.Id.quantity);
                if(textquantity.Text.ToString() == string.Empty)
                {
                    Console.WriteLine("ConfirmItem invalid quantity");
                    return;
                }
                iquantity = int.Parse(textquantity.Text.ToString());
                if (iquantity <= 0)
                {
                    Console.WriteLine("ConfirmItem invalid quantity");
                    return;
                }
                //have mainactivity post with entered quantity and ItemName & upc.
                var sendvalue = new Intent();
                var resultbundle = new Bundle();

                resultbundle.PutString("result_upc", str_upc);
                resultbundle.PutString("result_name", str_name);
                resultbundle.PutString("result_brand", str_brand);
                resultbundle.PutInt("result_quantity", iquantity);
                sendvalue.PutExtras(resultbundle);
                SetResult(Result.Ok, sendvalue);
                Finish();
            };
        }

        public void LoadImageLink(string simage)
        {

            //add image to activity page.
            ImageView imageview1 = FindViewById<ImageView>(Resource.Id.imageView1);
            var imageBitmap = GetImageBitmapFromUrl(simage);
            imageview1.SetImageBitmap(imageBitmap);
        }
        private Bitmap GetImageBitmapFromUrl(string url)
        {
            Bitmap imageBitmap = null;

            using (var webClient = new WebClient())
            {
                var imageBytes = webClient.DownloadData(url);
                if (imageBytes != null && imageBytes.Length > 0)
                {
                    imageBitmap = BitmapFactory.DecodeByteArray(imageBytes, 0, imageBytes.Length);
                }
            }

            return imageBitmap;

        }
    }
}
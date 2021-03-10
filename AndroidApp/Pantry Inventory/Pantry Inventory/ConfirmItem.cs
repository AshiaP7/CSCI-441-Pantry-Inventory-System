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
    [Activity(Label = "ConfirmItem")]
    public class ConfirmItem : Activity
    { 
        protected override void OnCreate(Bundle savedInstanceState)
        {
            base.OnCreate(savedInstanceState);

            // Create your application here
            SetContentView(Resource.Layout.ConfirmItem);
            LoadImageLink(Intent.GetStringExtra("image"));
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
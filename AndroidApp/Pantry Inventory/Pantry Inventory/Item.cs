using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using Android.App;
using Android.Content;
using Android.OS;
using Android.Runtime;
using Android.Views;
using Android.Widget;
using Newtonsoft.Json; //change

namespace Pantry_Inventory
{
    public class Item
    {
        [JsonProperty("title")]
        public string title { get; set; }

        [JsonProperty("upc")]
        public string upc { get; set; }
        [JsonProperty("images")]
        public string[] images { get; set; }
    }
}
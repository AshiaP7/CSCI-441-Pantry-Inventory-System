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
using Newtonsoft.Json;

namespace Pantry_Inventory
{
    class ItemList
    {
        /// <summary>
        /// A User's username. eg: "sergiotapia, mrkibbles, matumbo"
        /// </summary>
        [JsonProperty("code")]
        public string code { get; set; }

        /// <summary>
        /// A User's name. eg: "Sergio Tapia, John Cosack, Lucy McMillan"
        /// </summary>
        [JsonProperty("total")]
        public int total { get; set; }

        /// <summary>
        /// A User's location. eh: "Bolivia, USA, France, Italy"
        /// </summary>
        [JsonProperty("offset")]
        public string offset { get; set; }

        [JsonProperty("items")]
        public List<Item> Item { get; set; }

        //[JsonProperty("team")]
        // public string Team { get; set; } //Todo.

        /// <summary>
        /// A collection of the User's linked accounts.
        /// </summary>
        // [JsonProperty("accounts")]
        //public Account Accounts { get; set; }

        /// <summary>
        /// A collection of the User's awarded badges.
        /// </summary>
        //[JsonProperty("badges")]
        //public List<Badge> Badges { get; set; }
    }
}
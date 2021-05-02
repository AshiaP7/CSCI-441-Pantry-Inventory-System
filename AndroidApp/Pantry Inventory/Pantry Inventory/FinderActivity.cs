using Android.App;
using Android.Content;
using Android.OS;
using Android.Runtime;
using Android.Views;
using Android.Widget;
using System;
using System.Collections.Generic;
using System.Linq;
using System.IO;
using System.Text;

namespace com.example.pisrf
{
	using ActivityCompat = androidx.core.app.ActivityCompat;
	using FragmentActivity = androidx.fragment.app.FragmentActivity;

	using Settings = android.provider.Settings;
	using Location = android.location.Location;
	using LocationListener = android.location.LocationListener;
	using LocationManager = android.location.LocationManager;
	using Manifest = android.Manifest;
	using PackageManager = android.content.pm.PackageManager;
	using AsyncTask = android.os.AsyncTask;
	using Bundle = android.os.Bundle;
	using Log = android.util.Log;

	using GoogleMap = com.google.android.gms.maps.GoogleMap;
	using OnMapReadyCallback = com.google.android.gms.maps.OnMapReadyCallback;
	using SupportMapFragment = com.google.android.gms.maps.SupportMapFragment;
	using BitmapDescriptorFactory = com.google.android.gms.maps.model.BitmapDescriptorFactory;
	using LatLng = com.google.android.gms.maps.model.LatLng;
	using Marker = com.google.android.gms.maps.model.Marker;
	using MarkerOptions = com.google.android.gms.maps.model.MarkerOptions;

	using JSONArray = org.json.JSONArray;
	using JSONException = org.json.JSONException;
	using JSONObject = org.json.JSONObject;


	using CameraUpdateFactory = com.google.android.gms.maps.CameraUpdateFactory;

namespace Pantry_Inventory
{
    [Activity(Label = "FinderActivity")]
    public class FinderActivity : FragmentActivity, OnMapReadyCallback
	{

		private const string TAG = "polygon";
		private GoogleMap mMap;
		internal SupportMapFragment mapFrag;

		protected internal override void onCreate(Bundle savedInstanceState)
		{
			base.onCreate(savedInstanceState);
			setContentView(R.layout.activity_maps);
			mapFrag = (SupportMapFragment) getSupportFragmentManager().findFragmentById(R.id.map);
			mapFrag.getMapAsync(this);

		}




		public override void onMapReady(GoogleMap googleMap)
		{


			mMap = googleMap;

			try
			{
				if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED)
				{
					ActivityCompat.requestPermissions(this, new string[]{Manifest.permission.ACCESS_FINE_LOCATION}, 101);
				}
			}
			catch (Exception e)
			{
				Console.WriteLine(e.ToString());
				Console.Write(e.StackTrace);
			}




			mMap.setMyLocationEnabled(true);

			StringBuilder sbValue = new StringBuilder(sbMethod());
			PlacesTask placesTask = new PlacesTask(this);
			placesTask.execute(sbValue.ToString());
			//fix to use phone location and not artificial data
			LocationManager lm = (LocationManager) getSystemService((LOCATION_SERVICE));
			Location location = lm.getLastKnownLocation(LocationManager.GPS_PROVIDER);
			double longitude = location.getLongitude();
			double latitude = location.getLatitude();
			LatLng current_loc = new LatLng(latitude,longitude);
			mMap.animateCamera(CameraUpdateFactory.newLatLngZoom(current_loc, 18.0f));
			//mMap.moveCamera(CameraUpdateFactory.newLatLng(current_loc));
		}

		public virtual StringBuilder sbMethod()
		{
			try
			{
				if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED)
				{
					ActivityCompat.requestPermissions(this, new string[]{Manifest.permission.ACCESS_FINE_LOCATION}, 101);
				}
			}
			catch (Exception e)
			{
				Console.WriteLine(e.ToString());
				Console.Write(e.StackTrace);
			}

			//need to fix so it pulls phone location and uses that lat and long instead of artificial data
			LocationManager lm = (LocationManager) getSystemService((LOCATION_SERVICE));
			Location location = lm.getLastKnownLocation(LocationManager.GPS_PROVIDER);
			double longitude = location.getLongitude();
			double latitude = location.getLatitude();

			//creating list of locations to set apart as restaurant
			StringBuilder sb = new StringBuilder("https://maps.googleapis.com/maps/api/place/nearbysearch/json?");
			sb.Append("location=" + latitude + "," + longitude);
			sb.Append("&radius=5000");
			sb.Append("&types=" + "restaurant");
			sb.Append("&sensor=true");

			sb.Append("&key=AIzaSyCcTfucmrtRZd487Z3UTk6W2lcqO5HQM80");

			Log.d("Map", "url: " + sb.ToString());

			return sb;
		}

		private class PlacesTask : AsyncTask<string, int, string>
		{
			private readonly FinderActivity outerInstance;

			public PlacesTask(FinderActivity outerInstance)
			{
				this.outerInstance = outerInstance;
			}


			internal string data = null;

			protected internal override string doInBackground(params string[] url)
			{
				try
				{
					data = outerInstance.downloadUrl(url[0]);
				}
				catch (Exception e)
				{
					Log.d("Background Task", e.ToString());
				}
				return data;
			}

			protected internal override void onPostExecute(string result)
			{
				ParserTask parserTask = new ParserTask(outerInstance);

				parserTask.execute(result);
			}
		}

		private string downloadUrl(string strUrl)
		{
			string data = "";
			Stream iStream = null;
			HttpURLConnection urlConnection = null;
			try
			{
				URL url = new URL(strUrl);
				urlConnection = (HttpURLConnection) url.openConnection();
				urlConnection.connect();
				iStream = urlConnection.getInputStream();
				StreamReader br = new StreamReader(iStream);
				StringBuilder sb = new StringBuilder();
				string line = "";
				while (!string.ReferenceEquals((line = br.ReadLine()), null))
				{
					sb.Append(line);
				}

				data = sb.ToString();

				br.Close();

			}
			catch (Exception e)
			{
				Log.d("Exception", e.ToString());
			}
			finally
			{
				iStream.Close();
				urlConnection.disconnect();
			}
			return data;
		}

		private class ParserTask : AsyncTask<string, int, IList<Dictionary<string, string>>>
		{
			private readonly FinderActivity outerInstance;

			public ParserTask(FinderActivity outerInstance)
			{
				this.outerInstance = outerInstance;
			}


			internal JSONObject jObject;

			protected internal override IList<Dictionary<string, string>> doInBackground(params string[] jsonData)
			{

				IList<Dictionary<string, string>> places = null;
				Place_JSON placeJson = new Place_JSON(outerInstance);

				try
				{
					jObject = new JSONObject(jsonData[0]);
					places = placeJson.parse(jObject);
				}
				catch (Exception e)
				{
					Log.d("Exception", e.ToString());
				}
				return places;
			}

			protected internal override void onPostExecute(IList<Dictionary<string, string>> list)
			{

				Log.d("Map", "list size: " + list.Count);

				outerInstance.mMap.clear();

				for (int i = 0; i < list.Count; i++)
				{

					MarkerOptions markerOptions = new MarkerOptions();
					Dictionary<string, string> hmPlace = list[i];
					double lat = double.Parse(hmPlace["lat"]);
					double lng = double.Parse(hmPlace["lng"]);
					string name = hmPlace["place_name"];
					Log.d("Map", "place: " + name);
					string vicinity = hmPlace["vicinity"];
					LatLng latLng = new LatLng(lat, lng);
					markerOptions.position(latLng);
					markerOptions.title(name + " : " + vicinity);
					markerOptions.icon(BitmapDescriptorFactory.defaultMarker(BitmapDescriptorFactory.HUE_MAGENTA));
					Marker m = outerInstance.mMap.addMarker(markerOptions);
				}
			}
		}
		public class Place_JSON
		{
			private readonly FinderActivity outerInstance;

			public Place_JSON(FinderActivity outerInstance)
			{
				this.outerInstance = outerInstance;
			}



			public virtual IList<Dictionary<string, string>> parse(JSONObject jObject)
			{

				JSONArray jPlaces = null;
				try
				{

					jPlaces = jObject.getJSONArray("results");
				}
				catch (JSONException e)
				{
					Console.WriteLine(e.ToString());
					Console.Write(e.StackTrace);
				}

				return getPlaces(jPlaces);
			}

			internal virtual IList<Dictionary<string, string>> getPlaces(JSONArray jPlaces)
			{
				int placesCount = jPlaces.length();
				IList<Dictionary<string, string>> placesList = new List<Dictionary<string, string>>();
				Dictionary<string, string> place = null;

				for (int i = 0; i < placesCount; i++)
				{
					try
					{
						place = getPlace((JSONObject) jPlaces.get(i));
						placesList.Add(place);
					}
					catch (JSONException e)
					{
						Console.WriteLine(e.ToString());
						Console.Write(e.StackTrace);
					}
				}
				return placesList;
			}


			internal virtual Dictionary<string, string> getPlace(JSONObject jPlace)
			{

				Dictionary<string, string> place = new Dictionary<string, string>();
				string placeName = "-NA-";
				string vicinity = "-NA-";
				string latitude = "";
				string longitude = "";
				string reference = "";

				try
				{

					if (!jPlace.isNull("name"))
					{
						placeName = jPlace.getString("name");
					}

					if (!jPlace.isNull("vicinity"))
					{
						vicinity = jPlace.getString("vicinity");
					}

					latitude = jPlace.getJSONObject("geometry").getJSONObject("location").getString("lat");
					longitude = jPlace.getJSONObject("geometry").getJSONObject("location").getString("lng");
					reference = jPlace.getString("reference");

					place["place_name"] = placeName;
					place["vicinity"] = vicinity;
					place["lat"] = latitude;
					place["lng"] = longitude;
					place["reference"] = reference;

				}
				catch (JSONException e)
				{
					Console.WriteLine(e.ToString());
					Console.Write(e.StackTrace);
				}
				return place;
			}
		}

	}



}
	}
.



// All the working JAVA code is commented below

/*
 * 
 * MapsActivity.java
 * 
 * package com.example.pisrf;

import androidx.core.app.ActivityCompat;
import androidx.fragment.app.FragmentActivity;

import android.provider.Settings;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.Manifest;
import android.content.pm.PackageManager;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;

import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.MarkerOptions;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import com.google.android.gms.maps.CameraUpdateFactory;

public class MapsActivity extends FragmentActivity implements OnMapReadyCallback {

    private static final String TAG = "polygon";
    private GoogleMap mMap;
    SupportMapFragment mapFrag;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_maps);
        mapFrag = (SupportMapFragment) getSupportFragmentManager().findFragmentById(R.id.map);
        mapFrag.getMapAsync(this);

    }




    @Override
    public void onMapReady(GoogleMap googleMap) {


        mMap = googleMap;

        try {
            if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
                ActivityCompat.requestPermissions(this, new String[]{android.Manifest.permission.ACCESS_FINE_LOCATION}, 101);
            }
        } catch (Exception e){
            e.printStackTrace();
        }




        mMap.setMyLocationEnabled(true);

        StringBuilder sbValue = new StringBuilder(sbMethod());
        PlacesTask placesTask = new PlacesTask();
        placesTask.execute(sbValue.toString());
        //fix to use phone location and not artificial data
        LocationManager lm = (LocationManager) getSystemService((LOCATION_SERVICE));
        Location location = lm.getLastKnownLocation(LocationManager.GPS_PROVIDER);
        double longitude = location.getLongitude();
        double latitude = location.getLatitude();
        LatLng current_loc= new LatLng(latitude,longitude);
        mMap.animateCamera(CameraUpdateFactory.newLatLngZoom(current_loc, 18.0f));
        //mMap.moveCamera(CameraUpdateFactory.newLatLng(current_loc));
    }

    public StringBuilder sbMethod()
    {
        try {
            if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
                ActivityCompat.requestPermissions(this, new String[]{android.Manifest.permission.ACCESS_FINE_LOCATION}, 101);
            }
        } catch (Exception e){
            e.printStackTrace();
        }

        //need to fix so it pulls phone location and uses that lat and long instead of artificial data
        LocationManager lm = (LocationManager) getSystemService((LOCATION_SERVICE));
        Location location = lm.getLastKnownLocation(LocationManager.GPS_PROVIDER);
        double longitude = location.getLongitude();
        double latitude = location.getLatitude();

        //creating list of locations to set apart as restaurant
        StringBuilder sb = new StringBuilder("https://maps.googleapis.com/maps/api/place/nearbysearch/json?");
        sb.append("location=" + latitude + "," + longitude);
        sb.append("&radius=5000");
        sb.append("&types=" + "restaurant");
        sb.append("&sensor=true");

        sb.append("&key=AIzaSyCcTfucmrtRZd487Z3UTk6W2lcqO5HQM80");

        Log.d("Map", "url: " + sb.toString());

        return sb;
    }

    private class PlacesTask extends AsyncTask<String, Integer, String>
    {

        String data = null;

        @Override
        protected String doInBackground(String... url) {
            try {
                data = downloadUrl(url[0]);
            } catch (Exception e) {
                Log.d("Background Task", e.toString());
            }
            return data;
        }

        @Override
        protected void onPostExecute(String result) {
            ParserTask parserTask = new ParserTask();

            parserTask.execute(result);
        }
    }

    private String downloadUrl(String strUrl) throws IOException
    {
        String data = "";
        InputStream iStream = null;
        HttpURLConnection urlConnection = null;
        try {
            URL url = new URL(strUrl);
            urlConnection = (HttpURLConnection) url.openConnection();
            urlConnection.connect();
            iStream = urlConnection.getInputStream();
            BufferedReader br = new BufferedReader(new InputStreamReader(iStream));
            StringBuffer sb = new StringBuffer();
            String line = "";
            while ((line = br.readLine()) != null) {
                sb.append(line);
            }

            data = sb.toString();

            br.close();

        } catch (Exception e) {
            Log.d("Exception", e.toString());
        } finally {
            iStream.close();
            urlConnection.disconnect();
        }
        return data;
    }

    private class ParserTask extends AsyncTask<String, Integer, List<HashMap<String, String>>> {

        JSONObject jObject;

        @Override
        protected List<HashMap<String, String>> doInBackground(String... jsonData) {

            List<HashMap<String, String>> places = null;
            Place_JSON placeJson = new Place_JSON();

            try {
                jObject = new JSONObject(jsonData[0]);
                places = placeJson.parse(jObject);
            } catch (Exception e) {
                Log.d("Exception", e.toString());
            }
            return places;
        }

        @Override
        protected void onPostExecute(List<HashMap<String, String>> list) {

            Log.d("Map", "list size: " + list.size());

            mMap.clear();

            for (int i = 0; i < list.size(); i++) {

                MarkerOptions markerOptions = new MarkerOptions();
                HashMap<String, String> hmPlace = list.get(i);
                double lat = Double.parseDouble(hmPlace.get("lat"));
                double lng = Double.parseDouble(hmPlace.get("lng"));
                String name = hmPlace.get("place_name");
                Log.d("Map", "place: " + name);
                String vicinity = hmPlace.get("vicinity");
                LatLng latLng = new LatLng(lat, lng);
                markerOptions.position(latLng);
                markerOptions.title(name + " : " + vicinity);
                markerOptions.icon(BitmapDescriptorFactory.defaultMarker(BitmapDescriptorFactory.HUE_MAGENTA));
                Marker m = mMap.addMarker(markerOptions);
            }
        }
    }
    public class Place_JSON {


        public List<HashMap<String, String>> parse(JSONObject jObject) {

            JSONArray jPlaces = null;
            try {

                jPlaces = jObject.getJSONArray("results");
            } catch (JSONException e) {
                e.printStackTrace();
            }

            return getPlaces(jPlaces);
        }

        private List<HashMap<String, String>> getPlaces(JSONArray jPlaces) {
            int placesCount = jPlaces.length();
            List<HashMap<String, String>> placesList = new ArrayList<HashMap<String, String>>();
            HashMap<String, String> place = null;

            for (int i = 0; i < placesCount; i++) {
                try {
                    place = getPlace((JSONObject) jPlaces.get(i));
                    placesList.add(place);
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
            return placesList;
        }


        private HashMap<String, String> getPlace(JSONObject jPlace)
        {

            HashMap<String, String> place = new HashMap<String, String>();
            String placeName = "-NA-";
            String vicinity = "-NA-";
            String latitude = "";
            String longitude = "";
            String reference = "";

            try {

                if (!jPlace.isNull("name")) {
                    placeName = jPlace.getString("name");
                }

                if (!jPlace.isNull("vicinity")) {
                    vicinity = jPlace.getString("vicinity");
                }

                latitude = jPlace.getJSONObject("geometry").getJSONObject("location").getString("lat");
                longitude = jPlace.getJSONObject("geometry").getJSONObject("location").getString("lng");
                reference = jPlace.getString("reference");

                place.put("place_name", placeName);
                place.put("vicinity", vicinity);
                place.put("lat", latitude);
                place.put("lng", longitude);
                place.put("reference", reference);

            } catch (JSONException e) {
                e.printStackTrace();
            }
            return place;
        }
    }

}



 */

/*
 * 
 * AndroidManifest.xml
 * 
 * <?xml version="1.0" encoding="utf-8"?>
<manifest xmlns:android="http://schemas.android.com/apk/res/android"
    package="com.example.pisrf">


    <uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
    <uses-permission android:name="in.wptrafficanalyzer.locationgeocodingv2.permission.MAPS_RECEIVE" />
    <uses-permission android:name="android.permission.INTERNET" />
    <uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE" />
    <uses-permission android:name="com.google.android.providers.gsf.permission.READ_GSERVICES" />
    <uses-permission android:name="android.permission.ACCESS_BACKGROUND_LOCATION" />
    <uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />



    <uses-feature
        android:glEsVersion="0x00020000"
        android:required="true" />
    <meta-data
        android:name="com.google.android.maps.v2.API_KEY"
        android:value="AIzaSyCcTfucmrtRZd487Z3UTk6W2lcqO5HQM80" />

    <application
        android:allowBackup="true"
        android:icon="@mipmap/ic_launcher"
        android:label="@string/app_name"
        android:roundIcon="@mipmap/ic_launcher_round"
        android:supportsRtl="true"
        android:theme="@style/Theme.PISRF">

        <meta-data
            android:name="com.google.android.geo.API_KEY"
            android:value="AIzaSyCcTfucmrtRZd487Z3UTk6W2lcqO5HQM80" />

        <activity
            android:name=".MapsActivity"
            android:label="@string/title_activity_maps">
            <intent-filter>
                <action android:name="android.intent.action.MAIN" />

                <category android:name="android.intent.category.LAUNCHER" />
            </intent-filter>
        </activity>
    </application>

</manifest>
 * 
 */


/*
 *
 *activity_maps.xml
 * 
 * <?xml version="1.0" encoding="utf-8"?>
<LinearLayout xmlns:android="http://schemas.android.com/apk/res/android"
    android:orientation="vertical" android:layout_width="match_parent"
    android:layout_height="match_parent">

    <fragment xmlns:android="http://schemas.android.com/apk/res/android"
        xmlns:tools="http://schemas.android.com/tools"
        xmlns:map="http://schemas.android.com/apk/res-auto"
        android:layout_width="match_parent"
        android:layout_height="match_parent"
        android:id="@+id/map"
        tools:context="com.iotaconcepts.aurum.MapsActivity2"
        android:name="com.google.android.gms.maps.SupportMapFragment"/>

</LinearLayout>
 * 
 * 
 */
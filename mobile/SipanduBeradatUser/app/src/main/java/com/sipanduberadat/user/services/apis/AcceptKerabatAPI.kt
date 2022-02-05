package com.sipanduberadat.user.services.apis

import android.content.Context
import android.content.Intent
import android.view.View
import com.android.volley.Request
import com.google.gson.Gson
import com.sipanduberadat.user.activities.LoginActivity
import com.sipanduberadat.user.models.Kerabat
import com.sipanduberadat.user.services.FileDataPart
import com.sipanduberadat.user.services.VolleyMultipartRequest
import com.sipanduberadat.user.utils.Constants
import com.sipanduberadat.user.utils.getResponseData

fun acceptKerabatAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
                     fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
                     errorCallback: () -> Unit, showMessage: Boolean = true) {
    val sharedPreferences = context.getSharedPreferences("SESSIONS", Context.MODE_PRIVATE)
    val accessToken = sharedPreferences.getString("ACCESS_TOKEN", null)

    if (accessToken != null) {
        requestParams["XAT"] = "Bearer $accessToken"

        VolleyMultipartRequest(context, Request.Method.POST,
                "${Constants.BASE_URL}/kerabat/accept/", requestParams,
                fileRequestParams, {
            val dataString = getResponseData(it, contextView, context, requestParams,
                    fileRequestParams, callback, errorCallback, ::acceptKerabatAPI, showMessage)

            if (dataString != null) {
                callback(null)
            }
        }, { errorCallback() }).start()
        return
    }

    val intent = Intent(context, LoginActivity::class.java)
    intent.flags = Intent.FLAG_ACTIVITY_CLEAR_TASK or Intent.FLAG_ACTIVITY_NEW_TASK
    context.startActivity(intent)
}
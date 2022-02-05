package com.sipanduberadat.guest.services.apis

import android.content.Context
import android.content.Intent
import android.view.View
import com.android.volley.Request
import com.google.gson.Gson
import com.sipanduberadat.guest.activities.LoginActivity
import com.sipanduberadat.guest.models.Tamu
import com.sipanduberadat.guest.services.FileDataPart
import com.sipanduberadat.guest.services.VolleyMultipartRequest
import com.sipanduberadat.guest.utils.Constants
import com.sipanduberadat.guest.utils.getResponseData

fun changeAvatarAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
                fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
                errorCallback: () -> Unit, showMessage: Boolean = true) {
    val sharedPreferences = context.getSharedPreferences("SESSIONS", Context.MODE_PRIVATE)
    val accessToken = sharedPreferences.getString("ACCESS_TOKEN", null)

    if (accessToken != null) {
        requestParams["XAT"] = "Bearer $accessToken"

        VolleyMultipartRequest(context, Request.Method.POST,
            "${Constants.BASE_URL}/tamu/change-avatar/", requestParams,
            fileRequestParams, {
                val dataString = getResponseData(it, contextView, context, requestParams,
                    fileRequestParams, callback, errorCallback, ::changeAvatarAPI, showMessage)

                if (dataString != null) {
                    val tamu = Gson().fromJson(dataString, Tamu::class.java)
                    callback(tamu.avatar)
                }
            }, { errorCallback() }).start()
        return
    }

    val intent = Intent(context, LoginActivity::class.java)
    intent.flags = Intent.FLAG_ACTIVITY_CLEAR_TASK or Intent.FLAG_ACTIVITY_NEW_TASK
    context.startActivity(intent)
}
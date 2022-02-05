package com.sipanduberadat.petugas.services.apis

import android.content.Context
import android.view.View
import com.android.volley.Request
import com.sipanduberadat.petugas.services.FileDataPart
import com.sipanduberadat.petugas.services.VolleyMultipartRequest
import com.sipanduberadat.petugas.utils.Constants
import com.sipanduberadat.petugas.utils.getResponseData

fun sendOTPAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
          fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
          errorCallback: () -> Unit, showMessage: Boolean = true) {
    val sharedPreferences = context.getSharedPreferences("SESSIONS", Context.MODE_PRIVATE)
    val accessToken = sharedPreferences.getString("ACCESS_TOKEN", null)

    if (accessToken != null) {
        requestParams["XAT"] = "Bearer $accessToken"
    }

    VolleyMultipartRequest(context, Request.Method.POST,
        "${Constants.BASE_URL}/otp/send/", requestParams,
        fileRequestParams, {
            val dataString = getResponseData(it, contextView, context, requestParams,
                fileRequestParams, callback, errorCallback, ::sendOTPAPI, showMessage)

            if (dataString != null) {
                callback(null)
            }
        }, { errorCallback() }).start()
}
package com.sipanduberadat.petugas.services.apis

import android.content.Context
import android.view.View
import com.android.volley.Request
import com.google.gson.Gson
import com.sipanduberadat.petugas.models.Banjar
import com.sipanduberadat.petugas.services.FileDataPart
import com.sipanduberadat.petugas.services.VolleyMultipartRequest
import com.sipanduberadat.petugas.utils.Constants
import com.sipanduberadat.petugas.utils.getResponseData

fun findAllBanjarAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
                       fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
                     errorCallback: () -> Unit, showMessage: Boolean = true) {
    VolleyMultipartRequest(context, Request.Method.GET,
        "${Constants.BASE_URL}/banjar/?active_status=true", requestParams, fileRequestParams, {
            val dataString = getResponseData(it, contextView, context, requestParams,
                fileRequestParams, callback, errorCallback, ::findAllBanjarAPI, showMessage)

            if (dataString != null) {
                val banjar = Gson().fromJson(dataString, Array<Banjar>::class.java)
                callback(banjar)
            }
        }, { errorCallback() }).start()
}
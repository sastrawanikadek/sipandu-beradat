package com.sipanduberadat.petugas.services.apis

import android.content.Context
import android.view.View
import com.android.volley.Request
import com.google.gson.Gson
import com.sipanduberadat.petugas.models.Kabupaten
import com.sipanduberadat.petugas.services.FileDataPart
import com.sipanduberadat.petugas.services.VolleyMultipartRequest
import com.sipanduberadat.petugas.utils.Constants
import com.sipanduberadat.petugas.utils.getResponseData

fun findAllKabupatenAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
             fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
                        errorCallback: () -> Unit, showMessage: Boolean = true) {
    VolleyMultipartRequest(context, Request.Method.GET,
        "${Constants.BASE_URL}/kabupaten/?active_status=true", requestParams, fileRequestParams, {
            val dataString = getResponseData(it, contextView, context, requestParams,
                fileRequestParams, callback, errorCallback, ::findAllKabupatenAPI, showMessage)

            if (dataString != null) {
                val kabupaten = Gson().fromJson(dataString, Array<Kabupaten>::class.java)
                callback(kabupaten)
            }
        }, { errorCallback() }).start()
}
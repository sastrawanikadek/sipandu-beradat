package com.sipanduberadat.user.services.apis

import android.content.Context
import android.util.Log
import android.view.View
import com.android.volley.Request
import com.android.volley.Response
import com.google.gson.Gson
import com.sipanduberadat.user.models.Kabupaten
import com.sipanduberadat.user.models.Kecamatan
import com.sipanduberadat.user.services.FileDataPart
import com.sipanduberadat.user.services.VolleyMultipartRequest
import com.sipanduberadat.user.utils.Constants
import com.sipanduberadat.user.utils.getResponseData

fun findAllKecamatanAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
                        fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
                        errorCallback: () -> Unit, showMessage: Boolean = true) {
    VolleyMultipartRequest(context, Request.Method.GET,
        "${Constants.BASE_URL}/kecamatan/?active_status=true", requestParams, fileRequestParams, {
            val dataString = getResponseData(it, contextView, context, requestParams,
                fileRequestParams, callback, errorCallback, ::findAllKecamatanAPI, showMessage)

            if (dataString != null) {
                val kecamatan = Gson().fromJson(dataString, Array<Kecamatan>::class.java)
                callback(kecamatan)
            }
        }, { errorCallback() }).start()
}
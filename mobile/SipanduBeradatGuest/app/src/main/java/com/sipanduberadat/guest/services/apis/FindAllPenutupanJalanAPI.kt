package com.sipanduberadat.guest.services.apis

import android.content.Context
import android.content.Intent
import android.view.View
import com.android.volley.Request
import com.google.gson.GsonBuilder
import com.sipanduberadat.guest.activities.LoginActivity
import com.sipanduberadat.guest.models.PenutupanJalan
import com.sipanduberadat.guest.services.FileDataPart
import com.sipanduberadat.guest.services.VolleyMultipartRequest
import com.sipanduberadat.guest.utils.Constants
import com.sipanduberadat.guest.utils.getResponseData

fun findAllPenutupanJalanAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
                         fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
                         errorCallback: () -> Unit, showMessage: Boolean = true) {
    val sharedPreferences = context.getSharedPreferences("SESSIONS", Context.MODE_PRIVATE)
    val accessToken = sharedPreferences.getString("ACCESS_TOKEN", null)

    if (accessToken != null) {
        val params: MutableList<String> = mutableListOf()
        requestParams["XAT"] = "Bearer $accessToken"

        if (requestParams.containsKey("id_desa")) {
            params.add("id_desa=${requestParams["id_desa"]}")
            requestParams.remove("id_desa")
        }
        val queryString = params.joinToString(separator = "&")

        VolleyMultipartRequest(context, Request.Method.GET,
                "${Constants.BASE_URL}/penutupan-jalan/?$queryString", requestParams,
                fileRequestParams, {
            val dataString = getResponseData(it, contextView, context, requestParams,
                    fileRequestParams, callback, errorCallback, ::findAllPenutupanJalanAPI, showMessage)

            if (dataString != null) {
                val gson = GsonBuilder().setDateFormat("yyyy-MM-dd' 'HH:mm:ss").create()
                val blockedRoads = gson.fromJson(dataString, Array<PenutupanJalan>::class.java)
                callback(blockedRoads)
            }
        }, { errorCallback() }).start()
        return
    }

    val intent = Intent(context, LoginActivity::class.java)
    intent.flags = Intent.FLAG_ACTIVITY_CLEAR_TASK or Intent.FLAG_ACTIVITY_NEW_TASK
    context.startActivity(intent)
}
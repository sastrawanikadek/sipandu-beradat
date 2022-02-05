package com.sipanduberadat.guest.services.apis

import android.content.Context
import android.content.Intent
import android.util.Log
import android.view.View
import com.android.volley.Request
import com.android.volley.toolbox.HttpHeaderParser
import com.google.android.material.snackbar.Snackbar
import com.google.gson.Gson
import com.sipanduberadat.guest.activities.LoginActivity
import com.sipanduberadat.guest.models.Token
import com.sipanduberadat.guest.services.FileDataPart
import com.sipanduberadat.guest.services.VolleyMultipartRequest
import com.sipanduberadat.guest.utils.Constants
import com.sipanduberadat.guest.utils.snackbarError
import org.json.JSONObject

fun refreshTokenAPI(contextView: View, context: Context,
                    requestParams: HashMap<String, String>,
                    fileRequestParams: HashMap<String, FileDataPart>,
                    successCallback: (Any?) -> Unit,
                    errorCallback: () -> Unit,
                    callback: (
                        View, Context, HashMap<String, String>, HashMap<String, FileDataPart>,
                        (Any?) -> Unit, () -> Unit, Boolean) -> Unit, showMessage: Boolean = true) {
    val sharedPreferences = context.getSharedPreferences("SESSIONS", Context.MODE_PRIVATE)
    val refreshToken = sharedPreferences.getString("REFRESH_TOKEN", null)
    val onRefresh = sharedPreferences.getBoolean("ON_REFRESH", false)

    if (onRefresh) {
        callback(contextView, context, requestParams, fileRequestParams, successCallback,
                errorCallback, showMessage)
        return
    }

    if (refreshToken != null) {
        val editor = sharedPreferences.edit()
        editor.putBoolean("ON_REFRESH", true)
        if (!editor.commit()) return

        val rp = HashMap<String, String>()
        rp["XAT"] = "Bearer $refreshToken"

        VolleyMultipartRequest(context, Request.Method.POST,
            "${Constants.BASE_URL}/token/refresh/", rp, HashMap(), {
                val json = String(it.data, charset(HttpHeaderParser.parseCharset(it.headers)))
                Log.e("REFRESH JSON", json)
                val responseObject = JSONObject(json)
                editor.putBoolean("ON_REFRESH", false)
                editor.apply()

                when (responseObject.getInt("status_code")) {
                    200 -> {
                        val token: Token = Gson().fromJson(responseObject.getString("data"),
                                Token::class.java)
                        editor.putString("ACCESS_TOKEN", token.access_token)
                        editor.putString("REFRESH_TOKEN", token.refresh_token)
                        if (editor.commit()) callback(contextView, context, requestParams, fileRequestParams,
                                successCallback, errorCallback, showMessage)
                    }
                    401 -> {
                        editor.remove("ACCESS_TOKEN")
                        editor.remove("REFRESH_TOKEN")
                        editor.apply()

                        val intent = Intent(context, LoginActivity::class.java)
                        intent.flags = Intent.FLAG_ACTIVITY_CLEAR_TASK or Intent.FLAG_ACTIVITY_NEW_TASK
                        context.startActivity(intent)
                    }
                    else -> {
                        snackbarError(contextView, responseObject.getString("message"),
                                Snackbar.LENGTH_LONG).show()
                    }
                }
            }, {
                editor.putBoolean("ON_REFRESH", false)
                editor.apply()
                Log.e("ERROR", it.message!!)
            }).start()
        return
    }

    val intent = Intent(context, LoginActivity::class.java)
    intent.flags = Intent.FLAG_ACTIVITY_CLEAR_TASK or Intent.FLAG_ACTIVITY_NEW_TASK
    context.startActivity(intent)
}
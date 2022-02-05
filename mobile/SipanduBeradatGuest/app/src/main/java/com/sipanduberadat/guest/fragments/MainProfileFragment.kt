package com.sipanduberadat.guest.fragments

import android.app.Activity
import android.content.Context
import android.content.Intent
import android.content.SharedPreferences
import android.graphics.Bitmap
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.appcompat.app.AppCompatDelegate
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import com.bumptech.glide.Glide
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.activities.*
import com.sipanduberadat.guest.services.FileDataPart
import com.sipanduberadat.guest.services.apis.changeAvatarAPI
import com.sipanduberadat.guest.services.apis.logoutAPI
import com.sipanduberadat.guest.utils.choosePhoto
import com.sipanduberadat.guest.viewModels.MainViewModel
import kotlinx.android.synthetic.main.activity_main.*
import kotlinx.android.synthetic.main.layout_main_profile.view.*
import java.io.ByteArrayOutputStream
import java.util.*
import kotlin.collections.HashMap

class MainProfileFragment: Fragment() {
    private lateinit var sharedPreferences: SharedPreferences
    private lateinit var viewModel: MainViewModel

    private fun onChangeActivity(cls: Class<*>) {
        val intent = Intent(view!!.context, cls)
        intent.putExtra("ME", viewModel.me.value)
        startActivityForResult(intent, 2)
    }

    private fun onSuccessLogout(response: Any?) {
        if (response == null) {
            val sessionSharedPreferences = activity!!.getSharedPreferences("SESSIONS",
                Context.MODE_PRIVATE)
            val editor = sessionSharedPreferences.edit()
            editor.remove("ACCESS_TOKEN")
            editor.remove("REFRESH_TOKEN")
            editor.apply()

            val intent = Intent(view!!.context, LoginActivity::class.java)
            startActivity(intent)
            activity!!.finish()
        }
    }

    private fun onSuccessChangeAvatar(response: Any?) {
        if (response != null) {
            viewModel.me.value!!.avatar = response as String
            Glide.with(view!!.context).load(response).centerCrop().into(view!!.avatar)
        }
    }

    private fun onRequestError() {}

    private fun onToggleDayNight() {
        val mode: Int = if (sharedPreferences.getInt("DAY_NIGHT",
                AppCompatDelegate.MODE_NIGHT_FOLLOW_SYSTEM) == AppCompatDelegate.MODE_NIGHT_NO)
            AppCompatDelegate.MODE_NIGHT_YES else AppCompatDelegate.MODE_NIGHT_NO
        val editor = sharedPreferences.edit()
        editor.putInt("DAY_NIGHT", mode)
        editor.apply()
        AppCompatDelegate.setDefaultNightMode(mode)
    }

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_main_profile, container, false)
        sharedPreferences = activity!!.getSharedPreferences("PREFERENCES", Context.MODE_PRIVATE)
        viewModel = ViewModelProvider(activity!!).get(MainViewModel::class.java)

        viewModel.me.observe(activity!!, {
            if (it != null) {
                val locationText = "${it.negara.name} - ${it.akomodasi.name}"
                Glide.with(view.context).load(it.avatar).centerCrop().into(view.avatar)
                view.verified.visibility = View.VISIBLE
                view.name.text = it.name
                view.location.text = locationText
            }
        })

        view.btn_daynight.setImageResource(
            if (AppCompatDelegate.getDefaultNightMode() == AppCompatDelegate.MODE_NIGHT_NO)
                R.drawable.ic_moon else R.drawable.ic_sun)
        view.btn_daynight.setOnClickListener { onToggleDayNight() }

        view.avatar.setOnClickListener {
            val intentChooser = choosePhoto()
            startActivityForResult(intentChooser, 1)
        }
        view.btn_choose_photo.setOnClickListener {
            val intentChooser = choosePhoto()
            startActivityForResult(intentChooser, 1)
        }
        view.btn_logout.setOnClickListener {
            MaterialAlertDialogBuilder(view.context)
                    .setTitle("Log Out")
                    .setMessage("Are you sure to log out from this apps?")
                    .setPositiveButton("Cancel") { dialog, _ -> dialog.dismiss() }
                    .setNegativeButton("Sure") { _, _ ->
                        logoutAPI(activity!!.root, view.context, HashMap(), HashMap(),
                                this::onSuccessLogout, this::onRequestError)
                    }.show()
        }
        view.btn_to_profile.setOnClickListener { onChangeActivity(ProfileActivity::class.java) }
        view.btn_to_location.setOnClickListener { onChangeActivity(LocationActivity::class.java) }
        view.btn_to_change_password.setOnClickListener { onChangeActivity(VerifyCodeActivity::class.java) }

        return view
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        if (resultCode == Activity.RESULT_OK) {
            if (data != null) {
                if (requestCode == 1) {
                    if (data.data != null) {
                        val uri = data.data
                        val byteArray =
                            view!!.context.contentResolver.openInputStream(uri!!)?.buffered()?.use {
                                it.readBytes()
                            }
                        val fileRequestParams = HashMap<String, FileDataPart>()
                        fileRequestParams["avatar"] = FileDataPart(UUID.randomUUID().toString(),
                            byteArray!!, "image/jpeg")

                        changeAvatarAPI(activity!!.root, view!!.context, HashMap(), fileRequestParams,
                            this::onSuccessChangeAvatar, this::onRequestError)
                        return
                    }

                    val bitmap = data.extras!!.get("data") as Bitmap
                    val stream = ByteArrayOutputStream()
                    bitmap.compress(Bitmap.CompressFormat.JPEG, 100, stream)
                    val byteArray = stream.toByteArray()
                    val fileRequestParams = HashMap<String, FileDataPart>()
                    fileRequestParams["avatar"] = FileDataPart(UUID.randomUUID().toString(),
                        byteArray, "image/jpeg")

                    changeAvatarAPI(activity!!.root, view!!.context, HashMap(), fileRequestParams,
                        this::onSuccessChangeAvatar, this::onRequestError)
                } else if (requestCode == 2) {
                    viewModel.me.value = data.getParcelableExtra("ME")!!
                }
            }
        }
    }
}
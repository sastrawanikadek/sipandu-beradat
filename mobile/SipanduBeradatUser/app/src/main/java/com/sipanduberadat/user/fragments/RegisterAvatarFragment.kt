package com.sipanduberadat.user.fragments

import android.app.Activity
import android.content.Intent
import android.graphics.Bitmap
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import com.bumptech.glide.Glide
import com.sipanduberadat.user.R
import com.sipanduberadat.user.utils.choosePhoto
import com.sipanduberadat.user.viewModels.RegisterViewModel
import kotlinx.android.synthetic.main.layout_register_avatar.view.*
import java.io.ByteArrayOutputStream

class RegisterAvatarFragment: Fragment() {
    private lateinit var viewModel: RegisterViewModel

    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_register_avatar, container, false)
        viewModel = ViewModelProvider(activity!!).get(RegisterViewModel::class.java)

        view.avatar.setOnClickListener {
            val intentChooser = choosePhoto()
            startActivityForResult(intentChooser, 1)
        }

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
                        Glide.with(view!!.context).load(uri).centerCrop().into(view!!.avatar)
                        viewModel.avatar.value = byteArray
                        return
                    }

                    val bitmap = data.extras!!.get("data") as Bitmap
                    val stream = ByteArrayOutputStream()
                    bitmap.compress(Bitmap.CompressFormat.JPEG, 100, stream)
                    val byteArray = stream.toByteArray()
                    Glide.with(view!!.context).load(bitmap).centerCrop().into(view!!.avatar)
                    viewModel.avatar.value = byteArray
                }
            }
        }
    }
}
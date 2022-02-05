package com.sipanduberadat.user.activities

import android.content.Context
import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import androidx.lifecycle.ViewModelProvider
import androidx.viewpager.widget.ViewPager
import com.bumptech.glide.Glide
import com.google.android.material.snackbar.Snackbar
import com.google.android.material.textfield.MaterialAutoCompleteTextView
import com.google.android.material.textfield.TextInputEditText
import com.google.android.material.textfield.TextInputLayout
import com.google.android.material.textview.MaterialTextView
import com.google.firebase.messaging.FirebaseMessaging
import com.sipanduberadat.user.R
import com.sipanduberadat.user.adapters.KabupatenArrayAdapter
import com.sipanduberadat.user.adapters.RegisterViewPagerAdapter
import com.sipanduberadat.user.models.*
import com.sipanduberadat.user.services.FileDataPart
import com.sipanduberadat.user.services.apis.*
import com.sipanduberadat.user.utils.snackbarWarning
import com.sipanduberadat.user.viewModels.RegisterViewModel
import kotlinx.android.synthetic.main.activity_register.*
import kotlinx.android.synthetic.main.activity_register.btn_register
import kotlinx.android.synthetic.main.layout_register_avatar.view.*
import kotlinx.android.synthetic.main.layout_register_contact.view.*
import java.util.*
import kotlin.collections.HashMap

class RegisterActivity : AppCompatActivity() {
    private lateinit var viewModel: RegisterViewModel

    private fun onSuccessFCMToken(response: Any?) {
        if (response == null) {
            val intent = Intent(this, MainActivity::class.java)
            startActivity(intent)
            finish()
        }
    }

    private fun onRegisterSuccess(response: Any?) {
        if (response != null) {
            val token = response as Token
            val sharedPreferences = getSharedPreferences("SESSIONS", Context.MODE_PRIVATE)
            val editor = sharedPreferences.edit()
            editor.putString("ACCESS_TOKEN", token.access_token)
            editor.putString("REFRESH_TOKEN", token.refresh_token)
            editor.apply()

            FirebaseMessaging.getInstance().token.addOnSuccessListener {
                val requestParams: HashMap<String, String> = HashMap()
                requestParams["token"] = it

                fcmTokenAPI(root, this, requestParams, HashMap(),
                    this::onSuccessFCMToken, this::onRegisterRequestError, showMessage = false)
            }
        }
    }

    private fun onRegisterRequestError() { btn_register.stopProgress() }

    private fun onValidateRadio(message: String, isSuccess: Boolean) {
        val adapter = view_pager.adapter as RegisterViewPagerAdapter
        val fragment = adapter.getItem(view_pager.currentItem)
        val view = fragment.view

        if (view != null) {
            val helper: MaterialTextView = view.findViewById(R.id.gender_helper)

            if (isSuccess) {
                helper.text = ""
                helper.visibility = View.GONE
            } else {
                helper.text = message
                helper.visibility = View.VISIBLE
            }
        }
    }

    private fun onValidate(inputLayoutId: Int, editTextId: Int, message: String, isSuccess: Boolean,
                           isAutoComplete: Boolean = false) {
        val adapter = view_pager.adapter as RegisterViewPagerAdapter
        val fragment = adapter.getItem(view_pager.currentItem)
        val view = fragment.view

        if (view != null) {
            val inputLayout: TextInputLayout = view.findViewById(inputLayoutId)

            if (isSuccess) {
                inputLayout.helperText = ""
            } else {
                inputLayout.helperText = message
                if (isAutoComplete) {
                    val autoComplete: MaterialAutoCompleteTextView = view.findViewById(editTextId)
                    autoComplete.requestFocus()
                } else {
                    val editText: TextInputEditText = view.findViewById(editTextId)
                    editText.requestFocus()
                }
            }
        }
    }

    private fun onRegister() {
        when {
            viewModel.email.value.isNullOrBlank() -> {
                btn_register.stopProgress()
                onValidate(R.id.email_input_layout, R.id.email_edit_text,
                    "Alamat email tidak boleh kosong", false)
                return
            }
            !Regex("^[a-zA-Z0-9.!#\$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\\.[a-zA-Z0-9-]+)*\$")
                .matches(viewModel.email.value!!) -> {
                btn_register.stopProgress()
                onValidate(R.id.email_input_layout, R.id.email_edit_text,
                    "Alamat email tidak valid", false)
                return
            }
            else -> {
                onValidate(R.id.email_input_layout, R.id.email_edit_text,
                    "", true)
            }
        }

        when {
            viewModel.phone.value.isNullOrBlank() -> {
                btn_register.stopProgress()
                onValidate(
                    R.id.phone_input_layout, R.id.phone_edit_text, "No telepon tidak boleh kosong",
                    false
                )
                return
            }
            viewModel.phone.value!!.length < 10 || viewModel.phone.value!!.length > 13 -> {
                btn_register.stopProgress()
                onValidate(
                    R.id.phone_input_layout, R.id.phone_edit_text, "No telepon harus 10 sampai 13 angka",
                    false
                )
                return
            }
            else -> {
                onValidate(
                    R.id.phone_input_layout, R.id.phone_edit_text, "",
                    true
                )
            }
        }

        if (viewModel.kabupaten.value!! == (-1).toLong()) {
            btn_register.stopProgress()
            onValidate(R.id.kabupaten_input_layout, R.id.kabupaten_auto_complete, "Kabupaten tidak boleh kosong",
                isSuccess = false, isAutoComplete = true)
            return
        } else {
            onValidate(R.id.kabupaten_input_layout, R.id.kabupaten_auto_complete, "",
                isSuccess = true, isAutoComplete = true)
        }

        if (viewModel.kecamatan.value!! == (-1).toLong()) {
            btn_register.stopProgress()
            onValidate(R.id.kecamatan_input_layout, R.id.kecamatan_auto_complete, "Kecamatan tidak boleh kosong",
                isSuccess = false, isAutoComplete = true)
            return
        } else {
            onValidate(R.id.kecamatan_input_layout, R.id.kecamatan_auto_complete, "",
                isSuccess = true, isAutoComplete = true)
        }

        if (viewModel.desaAdat.value!! == (-1).toLong()) {
            btn_register.stopProgress()
            onValidate(R.id.desa_adat_input_layout, R.id.desa_adat_auto_complete, "Desa Adat tidak boleh kosong",
                isSuccess = false, isAutoComplete = true)
            return
        } else {
            onValidate(R.id.desa_adat_input_layout, R.id.desa_adat_auto_complete, "",
                isSuccess = true, isAutoComplete = true)
        }

        if (viewModel.banjar.value!! == (-1).toLong()) {
            btn_register.stopProgress()
            onValidate(R.id.banjar_input_layout, R.id.banjar_auto_complete, "Banjar tidak boleh kosong",
                isSuccess = false, isAutoComplete = true)
            return
        } else {
            onValidate(R.id.banjar_input_layout, R.id.banjar_auto_complete, "",
                isSuccess = true, isAutoComplete = true)
        }

        if (viewModel.homeLocation.value == null) {
            btn_register.stopProgress()
            snackbarWarning(root, "Lokasi rumah tidak boleh kosong",
                Snackbar.LENGTH_LONG).show()
            return
        }

        val requestParams = HashMap<String, String>()
        requestParams["id_banjar"] = "${viewModel.banjar.value}"
        requestParams["name"] = viewModel.name.value!!
        requestParams["email"] = viewModel.email.value!!
        requestParams["username"] = viewModel.username.value!!
        requestParams["password"] = viewModel.password.value!!
        requestParams["phone"] = viewModel.phone.value!!
        requestParams["date_of_birth"] = viewModel.dateOfBirth.value!!
        requestParams["nik"] = viewModel.nik.value!!
        requestParams["gender"] = viewModel.gender.value!!
        requestParams["category"] = viewModel.category.value!!
        requestParams["home_latitude"] = "${viewModel.homeLocation.value!!.latitude}"
        requestParams["home_longitude"] = "${viewModel.homeLocation.value!!.longitude}"

        val fileRequestParams = HashMap<String, FileDataPart>()
        if (viewModel.avatar.value != null) {
            fileRequestParams["avatar"] = FileDataPart(UUID.randomUUID().toString(),
                viewModel.avatar.value!!, "image/jpeg")
        }

        registerAPI(root, this, requestParams, fileRequestParams,
            this::onRegisterSuccess, this::onRegisterRequestError)
    }

    private fun onNextPage() {
        if (view_pager.currentItem == 0) {
            if (viewModel.name.value.isNullOrBlank()) {
                onValidate(R.id.name_input_layout, R.id.name_edit_text, "Nama lengkap tidak boleh kosong",
                        false)
                return
            } else {
                onValidate(R.id.name_input_layout, R.id.name_edit_text, "",
                        true)
            }

            if (viewModel.username.value.isNullOrBlank()) {
                onValidate(R.id.username_input_layout, R.id.username_edit_text, "Nama pengguna tidak boleh kosong",
                        false)
                return
            } else {
                onValidate(R.id.username_input_layout, R.id.username_edit_text, "",
                        true)
            }

            if (viewModel.password.value.isNullOrBlank()) {
                onValidate(R.id.password_input_layout, R.id.password_edit_text, "Kata sandi tidak boleh kosong",
                        false)
                return
            } else {
                onValidate(R.id.password_input_layout, R.id.password_edit_text, "",
                        true)
            }

            when {
                viewModel.nik.value.isNullOrBlank() -> {
                    onValidate(R.id.nik_input_layout, R.id.nik_edit_text, "NIK tidak boleh kosong",
                            false)
                    return
                }
                viewModel.nik.value!!.length != 16 -> {
                    onValidate(R.id.nik_input_layout, R.id.nik_edit_text, "NIK harus 16 angka",
                            false)
                    return
                }
                else -> {
                    onValidate(R.id.nik_input_layout, R.id.nik_edit_text, "", true)
                }
            }

            if (viewModel.gender.value.isNullOrBlank()) {
                onValidateRadio("Jenis kelamin tidak boleh kosong", false)
                return
            } else {
                onValidateRadio("", true)
            }

            if (viewModel.dateOfBirth.value.isNullOrBlank()) {
                onValidate(R.id.date_of_birth_input_layout, R.id.date_of_birth_edit_text, "Tanggal lahir tidak boleh kosong",
                        false)
                return
            } else {
                onValidate(R.id.date_of_birth_input_layout, R.id.date_of_birth_edit_text, "",
                        true)
            }

            if (viewModel.category.value.isNullOrBlank()) {
                onValidate(R.id.category_input_layout, R.id.category_auto_complete, "Kategori tidak boleh kosong",
                        isSuccess = false, isAutoComplete = true)
                return
            } else {
                onValidate(R.id.category_input_layout, R.id.category_auto_complete, "",
                        isSuccess = true, isAutoComplete = true)
            }
        }

        view_pager.currentItem += 1
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessBanjar(response: Any?) {
        if (response != null) {
            viewModel.banjars.value = (response as Array<Banjar>).toList()
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessDesaAdat(response: Any?) {
        if (response != null) {
            viewModel.desaAdats.value = (response as Array<DesaAdat>).toList()
            findAllBanjarAPI(root, this, HashMap(), HashMap(),
                    this::onSuccessBanjar, this::onRequestError, showMessage = false)
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessKecamatan(response: Any?) {
        if (response != null) {
            viewModel.kecamatans.value = (response as Array<Kecamatan>).toList()
            findAllDesaAdatAPI(root, this, HashMap(), HashMap(),
                    this::onSuccessDesaAdat, this::onRequestError, showMessage = false)
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessKabupaten(response: Any?) {
        if (response != null) {
            viewModel.kabupatens.value = (response as Array<Kabupaten>).toList()
            findAllKecamatanAPI(root, this, HashMap(), HashMap(),
                    this::onSuccessKecamatan, this::onRequestError, showMessage = false)
        }
    }

    private fun onRequestError() {}

    private fun onSkipPage() {
        if (view_pager.currentItem == 1) {
            viewModel.avatar.value = null
        }
        view_pager.currentItem += 1
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_register)

        viewModel = ViewModelProvider(this).get(RegisterViewModel::class.java)
        view_pager.adapter = RegisterViewPagerAdapter(supportFragmentManager)

        view_pager.addOnPageChangeListener(object: ViewPager.OnPageChangeListener {
            override fun onPageScrollStateChanged(state: Int) {}

            override fun onPageScrolled(position: Int, positionOffset: Float, positionOffsetPixels: Int) {}

            override fun onPageSelected(position: Int) {
                val adapter = view_pager.adapter as RegisterViewPagerAdapter
                val fragment = adapter.getItem(position)
                val view = fragment.view

                when (position) {
                    1 -> Glide.with(this@RegisterActivity)
                            .load(if (viewModel.gender.value!! == "l") R.drawable.ic_male else
                                R.drawable.ic_female).centerCrop().into(view!!.avatar)
                    adapter.count - 1 -> view!!.kabupaten_auto_complete.setAdapter(
                        KabupatenArrayAdapter(this@RegisterActivity, viewModel.kabupatens.value!!))
                }

                btn_skip.visibility = if (position == 1) View.VISIBLE else View.GONE
                btn_next.visibility = if (position != (view_pager.adapter as
                                RegisterViewPagerAdapter).count - 1) View.VISIBLE else View.GONE
                btn_register.visibility = if (position == (view_pager.adapter as
                                RegisterViewPagerAdapter).count - 1) View.VISIBLE else View.GONE
            }
        })

        btn_back.setOnClickListener { if (view_pager.currentItem == 0) finish() else view_pager.currentItem -= 1 }
        btn_skip.setOnClickListener { onSkipPage() }
        btn_next.setOnClickListener { onNextPage() }
        btn_register.setOnClickListener { onRegister() }

        findAllKabupatenAPI(root, this, HashMap(), HashMap(),
                this::onSuccessKabupaten, this::onRequestError, showMessage = false)
    }

    override fun onBackPressed() {
        if (view_pager.currentItem == 0) finish() else view_pager.currentItem -= 1
    }

}
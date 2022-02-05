package com.sipanduberadat.guest.activities

import android.content.Context
import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import androidx.lifecycle.ViewModelProvider
import androidx.viewpager.widget.ViewPager
import com.bumptech.glide.Glide
import com.google.android.material.textfield.MaterialAutoCompleteTextView
import com.google.android.material.textfield.TextInputEditText
import com.google.android.material.textfield.TextInputLayout
import com.google.android.material.textview.MaterialTextView
import com.google.firebase.messaging.FirebaseMessaging
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.adapters.RegisterViewPagerAdapter
import com.sipanduberadat.guest.models.Akomodasi
import com.sipanduberadat.guest.models.Negara
import com.sipanduberadat.guest.models.Token
import com.sipanduberadat.guest.services.FileDataPart
import com.sipanduberadat.guest.services.apis.fcmTokenAPI
import com.sipanduberadat.guest.services.apis.findAllAkomodasiAPI
import com.sipanduberadat.guest.services.apis.findAllNegaraAPI
import com.sipanduberadat.guest.services.apis.registerAPI
import com.sipanduberadat.guest.viewModels.RegisterViewModel
import kotlinx.android.synthetic.main.activity_register.*
import kotlinx.android.synthetic.main.layout_register_avatar.view.*
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
                        "E-mail cannot be empty", false)
                return
            }
            !Regex("^[a-zA-Z0-9.!#\$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\\.[a-zA-Z0-9-]+)*\$")
                    .matches(viewModel.email.value!!) -> {
                btn_register.stopProgress()
                onValidate(R.id.email_input_layout, R.id.email_edit_text,
                        "E-mail is invalid", false)
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
                        R.id.phone_input_layout, R.id.phone_edit_text, "Phone number cannot be empty",
                        false
                )
                return
            }
            viewModel.phone.value!!.length < 10 || viewModel.phone.value!!.length > 13 -> {
                btn_register.stopProgress()
                onValidate(
                        R.id.phone_input_layout, R.id.phone_edit_text, "Phone number must between 10 and 13 digits",
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

        if (viewModel.country.value!! == (-1).toLong()) {
            btn_register.stopProgress()
            onValidate(R.id.country_input_layout, R.id.country_auto_complete, "Country cannot be empty",
                    isSuccess = false, isAutoComplete = true)
            return
        } else {
            onValidate(R.id.country_input_layout, R.id.country_auto_complete, "",
                    isSuccess = true, isAutoComplete = true)
        }

        if (viewModel.accommodation.value!! == (-1).toLong()) {
            btn_register.stopProgress()
            onValidate(R.id.accommodation_input_layout, R.id.accommodation_auto_complete, "Accommodation cannot be empty",
                    isSuccess = false, isAutoComplete = true)
            return
        } else {
            onValidate(R.id.accommodation_input_layout, R.id.accommodation_auto_complete, "",
                    isSuccess = true, isAutoComplete = true)
        }

        val requestParams = HashMap<String, String>()
        requestParams["id_akomodasi"] = "${viewModel.accommodation.value}"
        requestParams["id_negara"] = "${viewModel.country.value}"
        requestParams["name"] = viewModel.name.value!!
        requestParams["email"] = viewModel.email.value!!
        requestParams["username"] = viewModel.username.value!!
        requestParams["password"] = viewModel.password.value!!
        requestParams["phone"] = viewModel.phone.value!!
        requestParams["date_of_birth"] = viewModel.dateOfBirth.value!!
        requestParams["identity_type"] = viewModel.identityType.value!!
        requestParams["identity_number"] = viewModel.identityNumber.value!!
        requestParams["gender"] = viewModel.gender.value!!

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
                onValidate(R.id.name_input_layout, R.id.name_edit_text, "Full name cannot be empty",
                        false)
                return
            } else {
                onValidate(R.id.name_input_layout, R.id.name_edit_text, "",
                        true)
            }

            if (viewModel.username.value.isNullOrBlank()) {
                onValidate(R.id.username_input_layout, R.id.username_edit_text, "Username cannot be empty",
                        false)
                return
            } else {
                onValidate(R.id.username_input_layout, R.id.username_edit_text, "",
                        true)
            }

            if (viewModel.password.value.isNullOrBlank()) {
                onValidate(R.id.password_input_layout, R.id.password_edit_text, "Password cannot be empty",
                        false)
                return
            } else {
                onValidate(R.id.password_input_layout, R.id.password_edit_text, "",
                        true)
            }

            if (viewModel.identityType.value.isNullOrBlank()) {
                onValidate(R.id.identity_type_input_layout, R.id.identity_type_auto_complete,
                    "Identity type cannot be empty", isSuccess = false, isAutoComplete = true)
                return
            } else {
                onValidate(R.id.identity_type_input_layout, R.id.identity_type_auto_complete, "",
                    isSuccess = true, isAutoComplete = true)
            }

            when {
                viewModel.identityNumber.value.isNullOrBlank() -> {
                    onValidate(R.id.identity_number_input_layout, R.id.identity_number_edit_text,
                        "Identity number cannot be empty", false)
                    return
                }
                else -> {
                    onValidate(R.id.identity_number_input_layout, R.id.identity_number_edit_text,
                        "", true)
                }
            }

            if (viewModel.gender.value.isNullOrBlank()) {
                onValidateRadio("Gender cannot be empty", false)
                return
            } else {
                onValidateRadio("", true)
            }

            if (viewModel.dateOfBirth.value.isNullOrBlank()) {
                onValidate(R.id.date_of_birth_input_layout, R.id.date_of_birth_edit_text,
                    "Date of birth cannot be empty", false)
                return
            } else {
                onValidate(R.id.date_of_birth_input_layout, R.id.date_of_birth_edit_text, "",
                        true)
            }
        }

        view_pager.currentItem += 1
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessAkomodasi(response: Any?) {
        if (response != null) {
            viewModel.accommodations.value = (response as Array<Akomodasi>).toList()
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessNegara(response: Any?) {
        if (response != null) {
            viewModel.countries.value = (response as Array<Negara>).toList()
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

        findAllNegaraAPI(root, this, HashMap(), HashMap(), this::onSuccessNegara,
            this::onRequestError, showMessage = false)
        findAllAkomodasiAPI(root, this, HashMap(), HashMap(), this::onSuccessAkomodasi,
            this::onRequestError, showMessage = false)
    }

    override fun onBackPressed() {
        if (view_pager.currentItem == 0) finish() else view_pager.currentItem -= 1
    }
}
package com.sipanduberadat.user.activities

import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.text.Editable
import android.text.TextWatcher
import android.view.View
import com.sipanduberadat.user.R
import com.sipanduberadat.user.services.apis.sendOTPAPI
import com.sipanduberadat.user.services.apis.verifyOTPAPI
import com.sipanduberadat.user.utils.getTimeCountdown
import kotlinx.android.synthetic.main.activity_verify_code.*
import java.util.*
import kotlin.collections.HashMap

class VerifyCodeActivity : AppCompatActivity() {
    private val code = mutableListOf("", "", "", "")
    private var id: String? = null
    private var role: String? = null
    private var action: String? = null
    private var handler: Handler? = null
    private var runnable: Runnable? = null
    private var sentTime = Calendar.getInstance()

    private fun onSuccessVerify(response: Any?) {
        if (response == null) {
            if (action != null && action == "forgot") {
                val intent = Intent(this, ForgotPasswordActivity::class.java)
                intent.putExtra("CODE", code.joinToString(separator = ""))
                intent.putExtra("ID", id)
                intent.putExtra("ROLE", role)
                startActivity(intent)
            } else {
                val intent = Intent(this, UpdatePasswordActivity::class.java)
                intent.putExtra("CODE", code.joinToString(separator = ""))
                startActivity(intent)
            }
            finish()
        }
    }

    private fun onVerifyCode() {
        val requestParams = HashMap<String, String>()
        requestParams["code"] = code.joinToString(separator = "")

        if (id != null && role != null) {
            requestParams["id"] = id!!
            requestParams["role"] = role!!
        }

        verifyOTPAPI(root, this@VerifyCodeActivity, requestParams, HashMap(),
            this@VerifyCodeActivity::onSuccessVerify, this@VerifyCodeActivity::onRequestError)
    }

    private fun onSuccessSend(response: Any?) {
        if (response == null) {
            countdown.text = ""
            sentTime = Calendar.getInstance()

            btn_resend.stopProgress()
            btn_resend.visibility = View.GONE
            resend_container.visibility = View.VISIBLE
            handler?.postDelayed(runnable!!, 1000)
        }
    }

    private fun onRequestError() { btn_resend.stopProgress() }

    private fun getRetryTime(): Calendar {
        return Calendar.getInstance().apply {
            time = sentTime.time
            add(Calendar.MINUTE, 1)
            add(Calendar.SECOND, 2)
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_verify_code)

        code_1_edit_text.addTextChangedListener(object: TextWatcher {
            override fun beforeTextChanged(p0: CharSequence?, p1: Int, p2: Int, p3: Int) {}

            override fun onTextChanged(p0: CharSequence?, p1: Int, p2: Int, p3: Int) {}

            override fun afterTextChanged(p0: Editable?) {
                code[0] = if ("$p0".replace(code[0], "").isBlank()) code[0] else
                    "$p0".replace(code[0], "")
                code_1_edit_text.removeTextChangedListener(this)
                code_1_edit_text.setText(code[0])
                code_1_edit_text.addTextChangedListener(this)

                if (code.filter { it.isNotEmpty() }.size == 4) {
                    onVerifyCode()
                } else {
                    code_2_edit_text.requestFocus()
                }
            }
        })

        code_2_edit_text.addTextChangedListener(object: TextWatcher {
            override fun beforeTextChanged(p0: CharSequence?, p1: Int, p2: Int, p3: Int) {}

            override fun onTextChanged(p0: CharSequence?, p1: Int, p2: Int, p3: Int) {}

            override fun afterTextChanged(p0: Editable?) {
                code[1] = if ("$p0".replace(code[1], "").isBlank()) code[1] else
                    "$p0".replace(code[1], "")
                code_2_edit_text.removeTextChangedListener(this)
                code_2_edit_text.setText(code[1])
                code_2_edit_text.addTextChangedListener(this)

                if (code.filter { it.isNotEmpty() }.size == 4) {
                    onVerifyCode()
                } else {
                    code_3_edit_text.requestFocus()
                }
            }
        })

        code_3_edit_text.addTextChangedListener(object: TextWatcher {
            override fun beforeTextChanged(p0: CharSequence?, p1: Int, p2: Int, p3: Int) {}

            override fun onTextChanged(p0: CharSequence?, p1: Int, p2: Int, p3: Int) {}

            override fun afterTextChanged(p0: Editable?) {
                code[2] = if ("$p0".replace(code[2], "").isBlank()) code[2] else
                    "$p0".replace(code[2], "")
                code_3_edit_text.removeTextChangedListener(this)
                code_3_edit_text.setText(code[2])
                code_3_edit_text.addTextChangedListener(this)

                if (code.filter { it.isNotEmpty() }.size == 4) {
                    onVerifyCode()
                } else {
                    code_4_edit_text.requestFocus()
                }
            }
        })

        code_4_edit_text.addTextChangedListener(object: TextWatcher {
            override fun beforeTextChanged(p0: CharSequence?, p1: Int, p2: Int, p3: Int) {}

            override fun onTextChanged(p0: CharSequence?, p1: Int, p2: Int, p3: Int) {}

            override fun afterTextChanged(p0: Editable?) {
                code[3] = if ("$p0".replace(code[3], "").isBlank()) code[3] else
                    "$p0".replace(code[3], "")
                code_4_edit_text.removeTextChangedListener(this)
                code_4_edit_text.setText(code[3])
                code_4_edit_text.addTextChangedListener(this)

                if (code.filter { it.isNotEmpty() }.size == 4) {
                    onVerifyCode()
                }
            }
        })

        val requestParams = HashMap<String, String>()

        if (intent.hasExtra("ID") && intent.hasExtra("ROLE") && intent.hasExtra("ACTION")) {
            id = intent.getStringExtra("ID")
            role = intent.getStringExtra("ROLE")
            action = intent.getStringExtra("ACTION")

            requestParams["id"] = "$id"
            requestParams["role"] = "$role"
        }

        sendOTPAPI(root, this, requestParams, HashMap(), this::onSuccessSend, this::onRequestError)

        handler = Handler(Looper.getMainLooper())
        runnable = object: Runnable {
            override fun run() {
                if (Calendar.getInstance().timeInMillis < getRetryTime().timeInMillis) {
                    countdown.text = getTimeCountdown(Calendar.getInstance().timeInMillis,
                        getRetryTime().timeInMillis, true)
                    handler?.postDelayed(this, 1000)
                } else {
                    resend_container.visibility = View.GONE
                    btn_resend.visibility = View.VISIBLE
                }
            }
        }
        handler?.postDelayed(runnable!!, 1000)

        btn_resend.setOnClickListener { sendOTPAPI(root, this, requestParams, HashMap(),
            this::onSuccessSend, this::onRequestError) }
        btn_back.setOnClickListener { finish() }
    }
}
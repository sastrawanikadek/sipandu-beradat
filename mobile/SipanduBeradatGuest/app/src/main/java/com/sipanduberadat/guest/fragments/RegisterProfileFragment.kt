package com.sipanduberadat.guest.fragments

import android.app.DatePickerDialog
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import androidx.core.widget.addTextChangedListener
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.viewModels.RegisterViewModel
import kotlinx.android.synthetic.main.layout_register_profile.view.*
import java.util.*

class RegisterProfileFragment: Fragment() {
    private val identityTypes: List<String> = listOf("Identity Card", "Driving License", "Passport")

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_register_profile, container, false)
        val viewModel = ViewModelProvider(activity!!).get(RegisterViewModel::class.java)

        view.name_edit_text.addTextChangedListener { viewModel.name.value = "$it" }
        view.username_edit_text.addTextChangedListener { viewModel.username.value = "$it" }
        view. password_edit_text.addTextChangedListener { viewModel.password.value = "$it" }
        view.identity_number_edit_text.addTextChangedListener { viewModel.identityNumber.value = "$it" }

        view.gender_radio_group.setOnCheckedChangeListener { _, _ ->
            viewModel.gender.value = if (view.gender_radio_group.checkedRadioButtonId == R.id.male_radio) "l" else "p"
        }

        view.identity_type_auto_complete.setAdapter(ArrayAdapter(view.context, R.layout.layout_item_list,
                identityTypes))
        view.identity_type_auto_complete.setOnItemClickListener { _, _, pos, _ ->
            viewModel.identityType.value = "$pos"
        }

        view.date_of_birth_edit_text.setOnClickListener {
            val builder = DatePickerDialog(view.context,
                    {_, year, month, dayOfMonth ->
                        viewModel.dateOfBirthDate.value = Calendar.getInstance().apply {
                            set(year, month, dayOfMonth)
                        }
                        val dateOfBirthText = "${year}-${(month + 1).toString().padStart(2,
                                '0')}-${dayOfMonth.toString().padStart(2, '0')}"
                        viewModel.dateOfBirth.value = dateOfBirthText
                        view.date_of_birth_edit_text.setText(dateOfBirthText)
                    },
                    viewModel.dateOfBirthDate.value!![Calendar.YEAR],
                    viewModel.dateOfBirthDate.value!![Calendar.MONTH],
                    viewModel.dateOfBirthDate.value!![Calendar.DAY_OF_MONTH])
            builder.datePicker.maxDate = Calendar.getInstance().timeInMillis
            builder.show()
        }

        return view
    }
}
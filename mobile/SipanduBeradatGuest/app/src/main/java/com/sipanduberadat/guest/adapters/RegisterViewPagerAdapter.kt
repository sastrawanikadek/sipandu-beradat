package com.sipanduberadat.guest.adapters

import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentManager
import androidx.fragment.app.FragmentPagerAdapter
import com.sipanduberadat.guest.fragments.RegisterAvatarFragment
import com.sipanduberadat.guest.fragments.RegisterContactFragment
import com.sipanduberadat.guest.fragments.RegisterProfileFragment

class RegisterViewPagerAdapter(fragmentManager: FragmentManager): FragmentPagerAdapter(fragmentManager,
    BEHAVIOR_RESUME_ONLY_CURRENT_FRAGMENT) {

    private val fragments = listOf(
        RegisterProfileFragment(),
        RegisterAvatarFragment(),
        RegisterContactFragment()
    )

    override fun getItem(position: Int): Fragment {
        return  fragments[position]
    }

    override fun getCount(): Int {
        return fragments.size
    }
}
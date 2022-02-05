package com.sipanduberadat.user.adapters

import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentManager
import androidx.fragment.app.FragmentPagerAdapter
import com.sipanduberadat.user.fragments.ReportDetailInfoFragment
import com.sipanduberadat.user.fragments.ReportDetailPecalangFragment
import com.sipanduberadat.user.fragments.ReportDetailPetugasFragment

class ReportDetailViewPagerAdapter(fragmentManager: FragmentManager): FragmentPagerAdapter(fragmentManager,
        BEHAVIOR_RESUME_ONLY_CURRENT_FRAGMENT) {
    private val fragments = listOf(
            ReportDetailInfoFragment(),
            ReportDetailPecalangFragment(),
            ReportDetailPetugasFragment()
    )

    override fun getCount(): Int {
        return fragments.size
    }

    override fun getItem(position: Int): Fragment {
        return fragments[position]
    }

    override fun getPageTitle(position: Int): CharSequence {
        return when (position) {
            0 -> "Informasi"
            1 -> "Pecalang"
            else -> "Petugas"
        }
    }
}